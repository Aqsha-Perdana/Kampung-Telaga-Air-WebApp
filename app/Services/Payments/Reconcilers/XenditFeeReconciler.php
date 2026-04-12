<?php

namespace App\Services\Payments\Reconcilers;

use App\DataTransferObjects\PaymentFeeSnapshot;
use App\Helpers\XenditPaymentHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class XenditFeeReconciler
{
    public function __construct(private readonly XenditPaymentHelper $xenditPaymentHelper)
    {
    }

    public function reconcile(Order $order): array
    {
        if ($order->payment_method !== 'xendit') {
            return ['snapshot' => null, 'reason' => 'not_xendit'];
        }

        try {
            $transaction = $this->findSettledTransactionForOrder($order);
        } catch (\Throwable $e) {
            Log::warning('Unable to reconcile Xendit gateway fee.', [
                'order_id' => $order->id_order,
                'error' => $e->getMessage(),
            ]);

            return ['snapshot' => null, 'reason' => 'lookup_failed'];
        }

        if (!$transaction) {
            return ['snapshot' => null, 'reason' => 'transaction_not_found'];
        }

        $snapshot = $this->resolveSettledTransactionSnapshot($transaction, $order);

        if (!$snapshot) {
            return ['snapshot' => null, 'reason' => 'not_ready'];
        }

        return ['snapshot' => $snapshot, 'reason' => null];
    }

    public function repairPendingActualFee(Order $order): ?PaymentFeeSnapshot
    {
        if ($order->payment_method !== 'xendit' || strtolower((string) ($order->gateway_fee_source ?? '')) !== 'actual') {
            return null;
        }

        $responseData = $this->normalizeResponseData(
            $order->paymentLogs()
                ->where('payment_method', 'xendit')
                ->where('status', 'success')
                ->latest('id')
                ->value('response_data')
        );

        $transaction = data_get($responseData, 'settled_transaction');
        if (!is_array($transaction) || $this->isFinalFeeState($transaction)) {
            return null;
        }

        $grossAmount = $this->toFloat($responseData['paid_amount'] ?? $responseData['amount'] ?? $order->base_amount) ?? (float) $order->base_amount;
        [$feeAmount, $netAmount] = $this->estimateXenditFeeFromPayload($responseData, $grossAmount);

        return new PaymentFeeSnapshot(
            paymentMethod: 'xendit',
            paymentChannel: $this->normalizeTransactionChannel(
                (string) (data_get($transaction, 'channel_code') ?? $responseData['payment_channel'] ?? ''),
                (string) ($order->payment_channel ?? '')
            ),
            grossAmount: $grossAmount,
            feeAmount: $feeAmount,
            netAmount: $netAmount,
            feeCurrency: strtoupper((string) ($responseData['currency'] ?? 'MYR')),
            feeSource: 'estimated',
            paymentLogResponseData: $responseData
        );
    }

    private function findSettledTransactionForOrder(Order $order): ?array
    {
        $response = $this->xenditPaymentHelper->listTransactions([
            'types' => 'PAYMENT',
            'statuses' => 'SUCCESS',
            'reference_id' => $order->id_order,
            'currency' => 'MYR',
            'limit' => 10,
        ]);

        return collect($response['data'] ?? [])
            ->sortByDesc(fn ($transaction) => (string) ($transaction['updated'] ?? $transaction['created'] ?? ''))
            ->first();
    }

    private function resolveSettledTransactionSnapshot(array $transaction, Order $order): ?PaymentFeeSnapshot
    {
        $settlementStatus = strtoupper((string) ($transaction['settlement_status'] ?? ''));
        $feeStatus = strtoupper((string) data_get($transaction, 'fee.status', ''));
        $grossAmount = $this->toFloat($transaction['amount'] ?? $order->base_amount) ?? (float) $order->base_amount;
        $netAmount = $this->toFloat($transaction['net_amount'] ?? null);

        if (!$this->isFinalFeeState($transaction)) {
            return null;
        }

        $feeAmount = $netAmount !== null
            ? max(0, $grossAmount - $netAmount)
            : (
                (float) data_get($transaction, 'fee.xendit_fee', 0)
                + (float) data_get($transaction, 'fee.value_added_tax', 0)
                + (float) data_get($transaction, 'fee.xendit_withholding_tax', 0)
                + (float) data_get($transaction, 'fee.third_party_withholding_tax', 0)
            );

        $existingResponseData = $this->normalizeResponseData(
            $order->paymentLogs()
                ->where('payment_method', 'xendit')
                ->where('status', 'success')
                ->latest('id')
                ->value('response_data')
        );
        $existingResponseData['settled_transaction'] = $transaction;

        return new PaymentFeeSnapshot(
            paymentMethod: 'xendit',
            paymentChannel: $this->normalizeTransactionChannel(
                (string) ($transaction['channel_code'] ?? ''),
                (string) ($order->payment_channel ?? '')
            ),
            grossAmount: $grossAmount,
            feeAmount: $feeAmount,
            netAmount: $netAmount,
            feeCurrency: strtoupper((string) ($transaction['currency'] ?? 'MYR')),
            feeSource: 'actual',
            paymentLogResponseData: $existingResponseData
        );
    }

    private function isFinalFeeState(array $transaction): bool
    {
        $settlementStatus = strtoupper((string) ($transaction['settlement_status'] ?? ''));
        $feeStatus = strtoupper((string) data_get($transaction, 'fee.status', ''));
        $netAmount = $this->toFloat($transaction['net_amount'] ?? null);

        return in_array($settlementStatus, ['SETTLED', 'EARLY_SETTLED'], true)
            && (
                $netAmount !== null
                || in_array($feeStatus, ['COMPLETED', 'NOT_APPLICABLE'], true)
            );
    }

    private function estimateXenditFeeFromPayload(array $payload, float $grossAmount): array
    {
        $channel = strtoupper((string) (
            $payload['payment_channel']
            ?? $payload['payment_method']
            ?? $payload['ewallet_type']
            ?? data_get($payload, 'settled_transaction.channel_code')
            ?? ''
        ));
        $normalizedChannel = $this->normalizeTransactionChannel($channel);
        $rules = config('payment.methods.xendit.reporting_fee_rules.channels.' . $normalizedChannel)
            ?? config('payment.methods.xendit.reporting_fee_rules.channels.' . strtoupper((string) ($payload['ewallet_type'] ?? '')))
            ?? config('payment.methods.xendit.reporting_fee_rules.default', []);

        $percentage = (float) ($rules['percentage'] ?? 0);
        $fixed = (float) ($rules['fixed'] ?? 0);
        $minimum = (float) ($rules['minimum'] ?? 0);

        $feeAmount = ($grossAmount * $percentage) + $fixed;
        $feeAmount = max($feeAmount, $minimum);
        $feeAmount = min($feeAmount, $grossAmount);
        $feeAmount = round($feeAmount, 2);
        $netAmount = round(max(0, $grossAmount - $feeAmount), 2);

        return [$feeAmount, $netAmount];
    }

    private function normalizeTransactionChannel(string $channelCode, string $fallback = ''): string
    {
        $channelCode = strtoupper(trim($channelCode));

        if ($channelCode === '') {
            return $fallback !== '' ? strtoupper($fallback) : 'xendit';
        }

        if (str_contains($channelCode, '_')) {
            $segments = explode('_', $channelCode);
            $candidate = end($segments);

            if ($candidate !== false && $candidate !== '') {
                return strtoupper((string) $candidate);
            }
        }

        return $channelCode;
    }

    private function normalizeResponseData(mixed $responseData): array
    {
        if (is_array($responseData)) {
            return $responseData;
        }

        if (is_string($responseData) && $responseData !== '') {
            $decoded = json_decode($responseData, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
