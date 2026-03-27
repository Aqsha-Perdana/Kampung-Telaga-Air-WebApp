<?php

namespace App\Services;

use App\Helpers\StripePaymentHelper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;

class StripeOrderService
{
    public function __construct(
        private readonly StripePaymentHelper $stripePaymentHelper,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    public function handleWebhook(string $payload, ?string $signature): void
    {
        Log::info('=== WEBHOOK RECEIVED ===');

        $event = $this->stripePaymentHelper->constructWebhookEvent($payload, $signature);
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

    private function handlePaymentSuccess(PaymentIntent $paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
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
                'payment_intent_id' => $paymentIntent->id,
            ]);

            PaymentLog::where('payment_intent_id', $paymentIntent->id)->update([
                'status' => 'success',
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

    private function mapPaymentLogStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'paid' => 'success',
            'cancelled' => 'failed',
            default => 'failed',
        };
    }
}
