<?php

namespace App\Services\Payments\Reconcilers;

use App\DataTransferObjects\PaymentFeeSnapshot;
use App\Helpers\StripePaymentHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class StripeFeeReconciler
{
    public function __construct(private readonly StripePaymentHelper $stripePaymentHelper)
    {
    }

    public function reconcile(Order $order): array
    {
        if ($order->payment_method !== 'stripe' || empty($order->payment_intent_id)) {
            return ['snapshot' => null, 'reason' => 'not_refreshable'];
        }

        try {
            $paymentIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);
            $snapshot = $this->resolveActualFeeSnapshot($paymentIntent);
        } catch (\Throwable $e) {
            Log::warning('Unable to reconcile Stripe gateway fee.', [
                'order_id' => $order->id_order,
                'payment_intent_id' => $order->payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            return ['snapshot' => null, 'reason' => 'lookup_failed'];
        }

        if (!$snapshot) {
            return ['snapshot' => null, 'reason' => 'not_ready'];
        }

        return ['snapshot' => $snapshot, 'reason' => null];
    }

    private function resolveActualFeeSnapshot(PaymentIntent $paymentIntent): ?PaymentFeeSnapshot
    {
        $receivedAmount = (float) (($paymentIntent->amount_received ?: $paymentIntent->amount ?: 0) / 100);
        $chargeId = $this->extractStripeId($paymentIntent->latest_charge ?? null);

        if ($chargeId === null) {
            return null;
        }

        try {
            $charge = $this->stripePaymentHelper->retrieveCharge($chargeId);
        } catch (\Throwable $e) {
            Log::warning('Failed retrieving Stripe charge during fee reconciliation.', [
                'payment_intent_id' => $paymentIntent->id ?? null,
                'charge_id' => $chargeId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $cardBrand = $charge->payment_method_details->card->brand ?? null;
        $paymentChannel = !empty($cardBrand)
            ? 'card_' . strtolower((string) $cardBrand)
            : 'card';

        $feeCurrency = strtoupper((string) ($charge->currency ?? $paymentIntent->currency ?? 'MYR'));
        $balanceTransactionId = $this->extractStripeId($charge->balance_transaction ?? null);

        try {
            if ($balanceTransactionId !== null) {
                $balanceTransaction = $this->stripePaymentHelper->retrieveBalanceTransaction($balanceTransactionId);

                return new PaymentFeeSnapshot(
                    paymentMethod: 'stripe',
                    paymentChannel: $paymentChannel,
                    grossAmount: $receivedAmount,
                    feeAmount: ((float) ($balanceTransaction->fee ?? 0)) / 100,
                    netAmount: ((float) ($balanceTransaction->net ?? 0)) / 100,
                    feeCurrency: strtoupper((string) ($balanceTransaction->currency ?? $feeCurrency)),
                    feeSource: 'actual',
                    paymentLogResponseData: json_decode(json_encode($paymentIntent), true)
                );
            }

            if (is_object($charge->balance_transaction ?? null)) {
                $balanceTransaction = $charge->balance_transaction;

                return new PaymentFeeSnapshot(
                    paymentMethod: 'stripe',
                    paymentChannel: $paymentChannel,
                    grossAmount: $receivedAmount,
                    feeAmount: ((float) ($balanceTransaction->fee ?? 0)) / 100,
                    netAmount: ((float) ($balanceTransaction->net ?? 0)) / 100,
                    feeCurrency: strtoupper((string) ($balanceTransaction->currency ?? $feeCurrency)),
                    feeSource: 'actual',
                    paymentLogResponseData: json_decode(json_encode($paymentIntent), true)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed retrieving Stripe balance transaction during fee reconciliation.', [
                'payment_intent_id' => $paymentIntent->id ?? null,
                'charge_id' => $chargeId,
                'balance_transaction_id' => $balanceTransactionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return null;
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
