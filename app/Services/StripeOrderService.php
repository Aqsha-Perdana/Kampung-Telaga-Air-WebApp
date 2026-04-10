<?php

namespace App\Services;

use App\Helpers\StripePaymentHelper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Stripe\PaymentIntent;

class StripeOrderService
{
    public function __construct(
        private readonly StripePaymentHelper $stripePaymentHelper,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    public function createPayment(Order $order, array $context = []): array
    {
        return [
            'client_secret' => $this->resolveStripeClientSecret(
                $order,
                (int) ($context['user_id'] ?? $order->user_id ?? 0),
                (float) $order->base_amount,
                (string) $order->customer_name,
                (string) $order->customer_email,
                (string) ($order->display_currency ?? 'MYR'),
                $order->display_amount !== null ? (float) $order->display_amount : null
            ),
        ];
    }

    public function handleWebhook(Request $request): void
    {
        Log::info('=== WEBHOOK RECEIVED ===');

        $event = $this->stripePaymentHelper->constructWebhookEvent(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );
        Log::info('Webhook signature verified', ['event' => $event->type]);

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
            case 'payment_intent.canceled':
                $this->handlePaymentCanceled($event->data->object);
                break;
        }
    }

    public function syncPendingOrder(Order $order): void
    {
        if ($order->status !== 'pending' || empty($order->payment_intent_id)) {
            return;
        }

        try {
            $paymentIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);

            switch ($paymentIntent->status) {
                case 'succeeded':
                    $this->handlePaymentSuccess($paymentIntent);
                    break;
                case 'canceled':
                    $this->handlePaymentCanceled($paymentIntent);
                    break;
                case 'requires_payment_method':
                    $this->handlePaymentFailed($paymentIntent);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Error checking Stripe payment status', [
                'order_id' => $order->id_order,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cancelPendingPayment(Order $order): bool
    {
        if ($order->status !== 'pending' || empty($order->payment_intent_id)) {
            return $order->status === 'pending';
        }

        try {
            $paymentIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                return false;
            }

            if ($paymentIntent->status !== 'canceled') {
                $paymentIntent = $this->stripePaymentHelper->cancelPaymentIntent($order->payment_intent_id);
            }

            return ($paymentIntent->status ?? null) !== 'succeeded';
        } catch (\Throwable $e) {
            Log::warning('Failed to cancel Stripe payment intent for pending order', [
                'order_id' => $order->id_order,
                'payment_intent_id' => $order->payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            try {
                $latestPaymentIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);

                if ($latestPaymentIntent->status === 'succeeded') {
                    return false;
                }

                return $latestPaymentIntent->status === 'canceled';
            } catch (\Throwable $syncError) {
                Log::warning('Failed to verify Stripe payment intent after cancel error', [
                    'order_id' => $order->id_order,
                    'payment_intent_id' => $order->payment_intent_id,
                    'error' => $syncError->getMessage(),
                ]);

                return false;
            }
        }
    }

    private function handlePaymentSuccess(PaymentIntent $paymentIntent): void
    {
        $paymentMetrics = $this->resolveStripePaymentMetrics($paymentIntent);

        DB::transaction(function () use ($paymentIntent, $paymentMetrics) {
            $orderId = $paymentIntent->metadata->order_id ?? null;

            if (!$orderId) {
                Log::error('No order_id in payment intent metadata');
                return;
            }

            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();

            if (!$order) {
                Log::error('Order not found for successful payment', ['order_id' => $orderId]);
                return;
            }

            if ($order->status === 'cancelled') {
                Log::warning('Ignoring successful Stripe payment for cancelled order', [
                    'order_id' => $orderId,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
                return;
            }

            if ($order->status === 'paid' && $order->redeem_code) {
                return;
            }

            $redeemCode = $order->redeem_code ?: $this->generateRedeemCode();

            $order->update([
                'status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
                'redeem_code' => $redeemCode,
                'payment_channel' => $paymentMetrics['payment_channel'],
                'payment_intent_id' => $paymentIntent->id,
                'gateway_fee_amount' => $paymentMetrics['fee_amount'],
                'gateway_fee_currency' => $paymentMetrics['fee_currency'],
                'gateway_net_amount' => $paymentMetrics['net_amount'],
                'gateway_fee_source' => $paymentMetrics['fee_source'],
            ]);

            PaymentLog::where('payment_intent_id', $paymentIntent->id)->update([
                'status' => 'success',
                'payment_channel' => $paymentMetrics['payment_channel'],
                'fee_amount' => $paymentMetrics['fee_amount'],
                'fee_currency' => $paymentMetrics['fee_currency'],
                'net_amount' => $paymentMetrics['net_amount'],
                'fee_source' => $paymentMetrics['fee_source'],
                'response_data' => json_encode($paymentIntent),
            ]);

            Cart::where('user_id', $order->user_id)->delete();
        });

        $orderId = $paymentIntent->metadata->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $order = Order::with('items')->where('id_order', $orderId)->first();
        if ($order && $order->status === 'paid') {
            $this->adminNotificationService->notifyPaymentPaid($order);
            $this->customerEmailService->sendOrderPaid($order);
        }
    }

    private function handlePaymentFailed(PaymentIntent $paymentIntent): void
    {
        $this->recordPaymentFailure($paymentIntent);
    }

    private function handlePaymentCanceled(PaymentIntent $paymentIntent): void
    {
        $this->updateOrderStatusFromPaymentIntent($paymentIntent, 'cancelled');
    }

    private function recordPaymentFailure(PaymentIntent $paymentIntent): void
    {
        try {
            $orderId = $paymentIntent->metadata->order_id ?? null;

            if (!$orderId) {
                return;
            }

            $order = Order::where('id_order', $orderId)->first();

            if (!$order || $order->status !== 'pending') {
                return;
            }

            PaymentLog::where('payment_intent_id', $paymentIntent->id)->update([
                'status' => 'failed',
                'response_data' => json_encode($paymentIntent),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed recording Stripe payment failure', [
                'payment_intent_id' => $paymentIntent->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function updateOrderStatusFromPaymentIntent(PaymentIntent $paymentIntent, string $status): void
    {
        try {
            $orderId = $paymentIntent->metadata->order_id ?? null;

            if (!$orderId) {
                return;
            }

            $order = Order::where('id_order', $orderId)->first();

            if (!$order) {
                return;
            }

            $order->update(['status' => $status]);

            PaymentLog::where('payment_intent_id', $paymentIntent->id)->update([
                'status' => $this->mapPaymentLogStatus($status),
                'response_data' => json_encode($paymentIntent),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed updating order status from Stripe payment intent', [
                'payment_intent_id' => $paymentIntent->id ?? null,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateRedeemCode(): string
    {
        return 'KTA-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);
    }

    private function resolveStripeClientSecret(
        Order $order,
        int $userId,
        float $baseAmount,
        string $customerName,
        string $customerEmail,
        string $displayCurrency,
        ?float $displayAmount
    ): string {
        $targetAmountInCents = (int) round($baseAmount * 100);

        if (!empty($order->payment_intent_id)) {
            try {
                $existingIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);

                if ($existingIntent->status === 'succeeded') {
                    throw new InvalidArgumentException('Payment for this order has already been completed.');
                }

                $amountMatches = (int) $existingIntent->amount === $targetAmountInCents;

                if (
                    in_array($existingIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'], true)
                    && $amountMatches
                    && !empty($existingIntent->client_secret)
                ) {
                    return (string) $existingIntent->client_secret;
                }

                if ($existingIntent->status === 'processing') {
                    throw new InvalidArgumentException('Your previous payment is still being processed. Please wait a moment before trying again.');
                }

                if ($existingIntent->status !== 'canceled') {
                    $this->stripePaymentHelper->cancelPaymentIntent($order->payment_intent_id);
                }
            } catch (InvalidArgumentException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::warning('Failed while resolving existing Stripe payment intent', [
                    'order_id' => $order->id_order,
                    'payment_intent_id' => $order->payment_intent_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $paymentIntent = $this->stripePaymentHelper->createPaymentIntent(
            $targetAmountInCents,
            $customerEmail,
            $this->buildStripeMetadata($order, $userId, $customerName, $baseAmount, $displayCurrency, $displayAmount)
        );

        $order->update(['payment_intent_id' => $paymentIntent->id]);

        PaymentLog::create([
            'id_order' => $order->id_order,
            'payment_intent_id' => $paymentIntent->id,
            'payment_method' => 'stripe',
            'amount' => $baseAmount,
            'currency' => 'MYR',
            'status' => 'pending',
            'response_data' => json_encode($paymentIntent),
        ]);

        return (string) $paymentIntent->client_secret;
    }

    private function buildStripeMetadata(
        Order $order,
        int $userId,
        string $customerName,
        float $baseAmount,
        string $displayCurrency,
        ?float $displayAmount
    ): array {
        return [
            'order_id' => $order->id_order,
            'user_id' => (string) $userId,
            'customer_name' => Str::limit($customerName, 100, ''),
            'base_amount' => (string) $baseAmount,
            'display_currency' => $displayCurrency,
            'display_amount' => (string) ($displayAmount ?? ''),
        ];
    }

    private function mapPaymentLogStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'paid' => 'success',
            'cancelled' => 'failed',
            default => 'failed',
        };
    }

    private function resolveStripePaymentMetrics(PaymentIntent $paymentIntent): array
    {
        $receivedAmount = (float) (($paymentIntent->amount_received ?: $paymentIntent->amount ?: 0) / 100);
        $metrics = [
            'fee_amount' => 0.0,
            'fee_currency' => strtoupper((string) ($paymentIntent->currency ?? 'MYR')),
            'net_amount' => round($receivedAmount, 2),
            'payment_channel' => 'card',
            'fee_source' => 'estimated',
        ];

        $chargeId = $this->extractStripeId($paymentIntent->latest_charge ?? null);
        if ($chargeId === null) {
            return $metrics;
        }

        try {
            $charge = $this->stripePaymentHelper->retrieveCharge($chargeId);

            $cardBrand = $charge->payment_method_details->card->brand ?? null;
            if (!empty($cardBrand)) {
                $metrics['payment_channel'] = 'card_' . strtolower((string) $cardBrand);
            }

            $metrics['fee_currency'] = strtoupper((string) ($charge->currency ?? $metrics['fee_currency']));

            $balanceTransactionId = $this->extractStripeId($charge->balance_transaction ?? null);
            if ($balanceTransactionId !== null) {
                $balanceTransaction = $this->stripePaymentHelper->retrieveBalanceTransaction($balanceTransactionId);
                $metrics['fee_amount'] = round(((float) ($balanceTransaction->fee ?? 0)) / 100, 2);
                $metrics['net_amount'] = round(((float) ($balanceTransaction->net ?? 0)) / 100, 2);
                $metrics['fee_currency'] = strtoupper((string) ($balanceTransaction->currency ?? $metrics['fee_currency']));
                $metrics['fee_source'] = 'actual';
            } elseif (is_object($charge->balance_transaction ?? null)) {
                $balanceTransaction = $charge->balance_transaction;
                $metrics['fee_amount'] = round(((float) ($balanceTransaction->fee ?? 0)) / 100, 2);
                $metrics['net_amount'] = round(((float) ($balanceTransaction->net ?? 0)) / 100, 2);
                $metrics['fee_currency'] = strtoupper((string) ($balanceTransaction->currency ?? $metrics['fee_currency']));
                $metrics['fee_source'] = 'actual';
            } else {
                $metrics['net_amount'] = round(max(0, $receivedAmount - $metrics['fee_amount']), 2);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve Stripe payment metrics', [
                'payment_intent_id' => $paymentIntent->id ?? null,
                'charge_id' => $chargeId,
                'error' => $e->getMessage(),
            ]);
        }

        $normalizedGatewayAmounts = resolve_gateway_amounts(
            $receivedAmount,
            $metrics['fee_amount'] ?? 0,
            $metrics['net_amount'] ?? null
        );
        $metrics['fee_amount'] = $normalizedGatewayAmounts['fee_amount'];
        $metrics['net_amount'] = $normalizedGatewayAmounts['net_amount'];

        return $metrics;
    }

    private function extractStripeId(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_object($value) && isset($value->id) && is_string($value->id) && $value->id !== '') {
            return $value->id;
        }

        if (is_array($value) && isset($value['id']) && is_string($value['id']) && $value['id'] !== '') {
            return $value['id'];
        }

        return null;
    }
}
