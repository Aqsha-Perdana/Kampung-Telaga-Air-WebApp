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

        if (
            $netAmount === null
            && !in_array($feeStatus, ['COMPLETED', 'NOT_APPLICABLE'], true)
            && !in_array($settlementStatus, ['SETTLED', 'EARLY_SETTLED'], true)
        ) {
            return null;
        }

        if (!in_array($settlementStatus, ['SETTLED', 'EARLY_SETTLED'], true) && $netAmount === null) {
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
