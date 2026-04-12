<?php

namespace App\Services\Payments;

use App\DataTransferObjects\PaymentFeeSnapshot;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Services\Payments\Reconcilers\StripeFeeReconciler;
use App\Services\Payments\Reconcilers\XenditFeeReconciler;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationService
{
    public function __construct(
        private readonly StripeFeeReconciler $stripeFeeReconciler,
        private readonly XenditFeeReconciler $xenditFeeReconciler
    ) {
    }

    public function refreshGatewayFeeIfAvailable(Order $order): array
    {
        if ($order->payment_method === 'xendit') {
            $repairSnapshot = $this->xenditFeeReconciler->repairPendingActualFee($order);
            if ($repairSnapshot) {
                $this->applySnapshot($order, $repairSnapshot);

                return ['updated' => true] + $repairSnapshot->toRefreshResult();
            }
        }

        $result = match ($order->payment_method) {
            'stripe' => $this->stripeFeeReconciler->reconcile($order),
            'xendit' => $this->xenditFeeReconciler->reconcile($order),
            default => ['snapshot' => null, 'reason' => 'not_reconcilable'],
        };

        /** @var PaymentFeeSnapshot|null $snapshot */
        $snapshot = $result['snapshot'] ?? null;
        if (!$snapshot) {
            return [
                'updated' => false,
                'reason' => $result['reason'] ?? 'not_ready',
            ];
        }

        $this->applySnapshot($order, $snapshot);

        return ['updated' => true] + $snapshot->toRefreshResult();
    }

    private function applySnapshot(Order $order, PaymentFeeSnapshot $snapshot): void
    {
        DB::transaction(function () use ($order, $snapshot) {
            $order->update($snapshot->toOrderAttributes());

            $paymentLog = PaymentLog::query()
                ->where('id_order', $order->id_order)
                ->where('payment_method', $order->payment_method)
                ->where('status', 'success')
                ->latest('id')
                ->first();

            if ($paymentLog) {
                $paymentLog->update($snapshot->toPaymentLogAttributes());
            }
        });
    }
}
