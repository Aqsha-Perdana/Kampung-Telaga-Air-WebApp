<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefundService
{
    public function __construct(
        private readonly AdminNotificationService $adminNotificationService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    /**
     * Approve refund request and process Stripe refund safely.
     *
     * @return array{
     *   ok: bool,
     *   code: string,
     *   message: string,
     *   refund_amount?: float,
     *   refund_fee?: float
     * }
     */
    public function approveRefund(string $orderId): array
    {
        $context = DB::transaction(function () use ($orderId) {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status === 'refunded' || $order->refund_status === 'succeeded') {
                return [
                    'state' => 'already_refunded',
                    'refund_amount' => (float) ($order->refund_amount ?? 0),
                    'refund_fee' => (float) ($order->refund_fee ?? 0),
                ];
            }

            if ($order->status !== 'refund_requested') {
                return ['state' => 'invalid_status'];
            }

            if ($order->refund_status === 'processing') {
                return ['state' => 'processing'];
            }

            $totalAmount = (float) $order->base_amount;
            $refundFee = $totalAmount * 0.10;
            $refundAmount = $totalAmount - $refundFee;

            $order->update([
                'refund_status' => 'processing',
                'refund_failure_reason' => null,
            ]);

            return [
                'state' => 'ready',
                'order_id' => $order->id_order,
                'payment_method' => $order->payment_method,
                'payment_intent_id' => $order->payment_intent_id,
                'refund_reason' => (string) $order->refund_reason,
                'refund_amount' => $refundAmount,
                'refund_fee' => $refundFee,
            ];
        });

        if (($context['state'] ?? null) !== 'ready') {
            return $this->buildNonReadyResult($context);
        }

        if (
            ($context['payment_method'] ?? null) !== 'stripe'
            || empty($context['payment_intent_id'])
        ) {
            $this->markFailed(
                $orderId,
                'Refund could not be processed because Stripe payment data is missing.'
            );

            return [
                'ok' => false,
                'code' => 'missing_payment_source',
                'message' => 'Refund failed because Stripe payment data is incomplete.',
            ];
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $refundAmountCents = (int) round(((float) $context['refund_amount']) * 100);
            $idempotencyKey = $this->buildIdempotencyKey(
                (string) $context['order_id'],
                (string) $context['payment_intent_id'],
                $refundAmountCents
            );

            $refund = \Stripe\Refund::create([
                'payment_intent' => $context['payment_intent_id'],
                'amount' => $refundAmountCents,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'order_id' => $context['order_id'],
                    'reason' => $context['refund_reason'],
                    'fee_deducted' => (string) $context['refund_fee'],
                ],
            ], [
                'idempotency_key' => $idempotencyKey,
            ]);

            DB::transaction(function () use ($orderId, $context, $refund): void {
                $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
                if (!$order || $order->refund_status !== 'processing') {
                    return;
                }

                $order->update([
                    'status' => 'refunded',
                    'refund_status' => 'succeeded',
                    'refund_amount' => $context['refund_amount'],
                    'refund_fee' => $context['refund_fee'],
                    'stripe_refund_id' => $refund->id,
                    'refunded_at' => now(),
                    'refund_failure_reason' => null,
                ]);
            });

            $order = Order::with('items')->where('id_order', $orderId)->first();
            if ($order) {
                $this->adminNotificationService->notifyRefundProcessed($order);
                $this->customerEmailService->sendRefundApproved($order);
            }

            return [
                'ok' => true,
                'code' => 'succeeded',
                'message' => 'Refund has been processed successfully.',
                'refund_amount' => (float) $context['refund_amount'],
                'refund_fee' => (float) $context['refund_fee'],
            ];
        } catch (\Throwable $e) {
            $this->markFailed($orderId, $e->getMessage());

            return [
                'ok' => false,
                'code' => 'gateway_error',
                'message' => 'Failed to process refund via Stripe: ' . $e->getMessage(),
            ];
        }
    }

    private function buildNonReadyResult(array $context): array
    {
        return match ($context['state'] ?? null) {
            'already_refunded' => [
                'ok' => true,
                'code' => 'already_refunded',
                'message' => 'This refund has already been processed.',
                'refund_amount' => (float) ($context['refund_amount'] ?? 0),
                'refund_fee' => (float) ($context['refund_fee'] ?? 0),
            ],
            'processing' => [
                'ok' => false,
                'code' => 'processing',
                'message' => 'Refund is currently being processed. Please wait a moment.',
            ],
            default => [
                'ok' => false,
                'code' => 'invalid_status',
                'message' => 'This order is not in refund-requested status.',
            ],
        };
    }

    private function markFailed(string $orderId, string $reason): void
    {
        DB::transaction(function () use ($orderId, $reason): void {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
            if (!$order || $order->status !== 'refund_requested') {
                return;
            }

            $order->update([
                'refund_status' => 'failed',
                'refund_failure_reason' => Str::limit($reason, 1000),
            ]);
        });
    }

    private function buildIdempotencyKey(string $orderId, string $paymentIntentId, int $refundAmountCents): string
    {
        return 'refund_' . $orderId . '_' . $paymentIntentId . '_' . $refundAmountCents;
    }
}
