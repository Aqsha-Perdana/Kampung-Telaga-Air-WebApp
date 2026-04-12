<?php

namespace App\Services;

use App\Helpers\XenditPaymentHelper;
use App\Models\Order;
use App\Models\PaymentLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefundService
{
    public function __construct(
        private readonly XenditPaymentHelper $xenditPaymentHelper,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    /**
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
            $refundFee = round($totalAmount * 0.10, 2);
            $refundAmount = round(max(0, $totalAmount - $refundFee), 2);

            $xenditContext = $order->payment_method === 'xendit'
                ? $this->buildXenditRefundContext($order)
                : [];

            $order->update([
                'refund_status' => 'processing',
                'refund_failure_reason' => null,
            ]);

            return array_merge([
                'state' => 'ready',
                'order_id' => $order->id_order,
                'payment_method' => $order->payment_method,
                'payment_intent_id' => $order->payment_intent_id,
                'refund_reason' => (string) $order->refund_reason,
                'refund_amount' => $refundAmount,
                'refund_fee' => $refundFee,
                'paid_at' => $order->paid_at,
                'payment_channel' => (string) ($order->payment_channel ?? ''),
            ], $xenditContext);
        });

        if (($context['state'] ?? null) !== 'ready') {
            return $this->buildNonReadyResult($context);
        }

        return match ($context['payment_method'] ?? null) {
            'stripe' => $this->processStripeRefund($orderId, $context),
            'xendit' => $this->processXenditRefund($orderId, $context),
            default => $this->buildMissingPaymentSourceResult(
                $orderId,
                'Refund could not be processed because payment data is missing or unsupported.'
            ),
        };
    }

    public function finalizeXenditRefundSucceeded(array $payload): void
    {
        $refundData = $this->normalizeXenditRefundPayload($payload);
        $orderId = $this->resolveXenditRefundOrderId($refundData);

        if ($orderId === '') {
            return;
        }

        DB::transaction(function () use ($orderId, $payload, $refundData): void {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
            if (!$order) {
                return;
            }

            $order->update([
                'status' => 'refunded',
                'refund_status' => 'succeeded',
                'xendit_refund_id' => (string) ($refundData['id'] ?? $order->xendit_refund_id),
                'refunded_at' => Carbon::parse((string) ($refundData['updated'] ?? $refundData['created'] ?? now())),
                'refund_failure_reason' => null,
            ]);

            $this->appendRefundPayloadToPaymentLog($order, 'xendit_refund_result', $payload);
        });

        $order = Order::with('items')->where('id_order', $orderId)->first();
        if ($order) {
            $this->adminNotificationService->notifyRefundProcessed($order);
            $this->customerEmailService->sendRefundApproved($order);
        }
    }

    public function finalizeXenditRefundFailed(array $payload): void
    {
        $refundData = $this->normalizeXenditRefundPayload($payload);
        $orderId = $this->resolveXenditRefundOrderId($refundData);

        if ($orderId === '') {
            return;
        }

        $failureMessage = trim((string) (($refundData['failure_code'] ?? '') . ' ' . ($refundData['status'] ?? 'FAILED')));
        $failureMessage = $failureMessage !== '' ? $failureMessage : 'Xendit refund request failed.';

        DB::transaction(function () use ($orderId, $payload, $refundData, $failureMessage): void {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
            if (!$order) {
                return;
            }

            $order->update([
                'status' => 'paid',
                'refund_status' => 'failed',
                'xendit_refund_id' => (string) ($refundData['id'] ?? $order->xendit_refund_id),
                'refund_failure_reason' => Str::limit($failureMessage, 1000),
            ]);

            $this->appendRefundPayloadToPaymentLog($order, 'xendit_refund_result', $payload);
        });
    }

    private function processStripeRefund(string $orderId, array $context): array
    {
        if (empty($context['payment_intent_id'])) {
            return $this->buildMissingPaymentSourceResult(
                $orderId,
                'Refund failed because Stripe payment data is incomplete.'
            );
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

    private function processXenditRefund(string $orderId, array $context): array
    {
        $paymentRequestId = (string) ($context['xendit_payment_request_id'] ?? '');
        if ($paymentRequestId === '') {
            return $this->buildMissingPaymentSourceResult(
                $orderId,
                'Refund failed because Xendit payment request data is incomplete.'
            );
        }

        $eligibility = $this->resolveXenditRefundEligibility($context);
        if (($eligibility['ok'] ?? false) !== true) {
            $this->markFailed($orderId, (string) ($eligibility['message'] ?? 'Xendit refund is not eligible.'));

            return [
                'ok' => false,
                'code' => 'xendit_refund_ineligible',
                'message' => (string) ($eligibility['message'] ?? 'Xendit refund is not eligible.'),
            ];
        }

        try {
            $refundResponse = $this->xenditPaymentHelper->createRefund([
                'reference_id' => $this->buildXenditRefundReferenceId((string) $context['order_id']),
                'payment_request_id' => $paymentRequestId,
                'currency' => 'MYR',
                'amount' => round((float) $context['refund_amount'], 2),
                'reason' => 'REQUESTED_BY_CUSTOMER',
                'metadata' => [
                    'order_id' => (string) $context['order_id'],
                    'refund_reason' => (string) $context['refund_reason'],
                    'refund_fee' => (string) $context['refund_fee'],
                ],
            ], $this->buildIdempotencyKey(
                (string) $context['order_id'],
                $paymentRequestId,
                (int) round(((float) $context['refund_amount']) * 100)
            ));
        } catch (\Throwable $e) {
            $this->markFailed($orderId, $e->getMessage());

            return [
                'ok' => false,
                'code' => 'gateway_error',
                'message' => 'Failed to submit refund via Xendit: ' . $e->getMessage(),
            ];
        }

        DB::transaction(function () use ($orderId, $context, $refundResponse): void {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
            if (!$order || $order->refund_status !== 'processing') {
                return;
            }

            $status = strtoupper((string) ($refundResponse['status'] ?? 'PENDING'));
            $isSucceeded = $status === 'SUCCEEDED';

            $order->update([
                'status' => $isSucceeded ? 'refunded' : 'refund_requested',
                'refund_status' => $isSucceeded ? 'succeeded' : 'processing',
                'refund_amount' => $context['refund_amount'],
                'refund_fee' => $context['refund_fee'],
                'xendit_refund_id' => $refundResponse['id'] ?? null,
                'refunded_at' => $isSucceeded ? now() : null,
                'refund_failure_reason' => null,
            ]);

            $this->appendRefundPayloadToPaymentLog($order, 'xendit_refund_request', $refundResponse);
        });

        if (strtoupper((string) ($refundResponse['status'] ?? 'PENDING')) === 'SUCCEEDED') {
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
        }

        return [
            'ok' => true,
            'code' => 'processing',
            'message' => 'Refund request was submitted to Xendit and is waiting for final webhook confirmation.',
            'refund_amount' => (float) $context['refund_amount'],
            'refund_fee' => (float) $context['refund_fee'],
        ];
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

    private function buildMissingPaymentSourceResult(string $orderId, string $message): array
    {
        $this->markFailed($orderId, $message);

        return [
            'ok' => false,
            'code' => 'missing_payment_source',
            'message' => $message,
        ];
    }

    private function markFailed(string $orderId, string $reason): void
    {
        DB::transaction(function () use ($orderId, $reason): void {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();
            if (!$order || !in_array($order->status, ['refund_requested', 'paid'], true)) {
                return;
            }

            $order->update([
                'status' => 'paid',
                'refund_status' => 'failed',
                'refund_failure_reason' => Str::limit($reason, 1000),
            ]);
        });
    }

    private function buildXenditRefundContext(Order $order): array
    {
        $paymentLog = $order->paymentLogs()
            ->where('payment_method', 'xendit')
            ->where('status', 'success')
            ->latest('id')
            ->first();

        $responseData = $paymentLog?->response_data ?? [];
        $settledTransaction = data_get($responseData, 'settled_transaction', []);

        return [
            'xendit_payment_request_id' => (string) (
                data_get($settledTransaction, 'product_data.payment_request_id')
                ?: data_get($responseData, 'payment_request_id')
                ?: ''
            ),
            'xendit_channel_code' => strtoupper((string) (
                data_get($settledTransaction, 'channel_code')
                ?: data_get($responseData, 'payment_channel')
                ?: $order->payment_channel
                ?: ''
            )),
        ];
    }

    private function resolveXenditRefundEligibility(array $context): array
    {
        $channelCode = strtoupper((string) ($context['xendit_channel_code'] ?? ''));
        $rules = config('payment.methods.xendit.refund_rules.channels.' . $channelCode)
            ?? config('payment.methods.xendit.refund_rules.channels.' . strtoupper((string) ($context['payment_channel'] ?? '')))
            ?? config('payment.methods.xendit.refund_rules.default', []);

        if (!(bool) ($rules['enabled'] ?? false)) {
            return [
                'ok' => false,
                'message' => 'This Xendit payment channel does not support refunds from the application.',
            ];
        }

        if (!(bool) ($rules['partial_refund'] ?? false)) {
            return [
                'ok' => false,
                'message' => 'This Xendit payment channel does not support partial refunds, while the current refund policy deducts a 10% fee.',
            ];
        }

        $validityDays = (int) ($rules['validity_days'] ?? 0);
        $paidAt = $context['paid_at'] ?? null;

        if ($validityDays > 0 && $paidAt instanceof Carbon && $paidAt->copy()->addDays($validityDays)->lt(now())) {
            return [
                'ok' => false,
                'message' => 'The Xendit refund validity window for this payment channel has expired.',
            ];
        }

        return ['ok' => true];
    }

    private function normalizeXenditRefundPayload(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data']) && isset($payload['data']['data']) && is_array($payload['data']['data'])) {
            return $payload['data']['data'];
        }

        if (isset($payload['data']) && is_array($payload['data']) && isset($payload['data']['id'])) {
            return $payload['data'];
        }

        return $payload;
    }

    private function resolveXenditRefundOrderId(array $refundData): string
    {
        $metadataOrderId = (string) data_get($refundData, 'metadata.order_id', '');
        if ($metadataOrderId !== '') {
            return $metadataOrderId;
        }

        $referenceId = (string) ($refundData['reference_id'] ?? '');
        if (str_starts_with($referenceId, 'refund_')) {
            $parts = explode('_', $referenceId);
            if (isset($parts[1]) && str_starts_with($parts[1], 'ORD-')) {
                return $parts[1];
            }
        }

        $refundId = (string) ($refundData['id'] ?? '');
        if ($refundId !== '') {
            return (string) (Order::where('xendit_refund_id', $refundId)->value('id_order') ?? '');
        }

        return '';
    }

    private function appendRefundPayloadToPaymentLog(Order $order, string $key, array $payload): void
    {
        $paymentLog = PaymentLog::query()
            ->where('id_order', $order->id_order)
            ->where('payment_method', $order->payment_method)
            ->where('status', 'success')
            ->latest('id')
            ->first();

        if (!$paymentLog) {
            return;
        }

        $responseData = is_array($paymentLog->response_data) ? $paymentLog->response_data : [];
        $responseData[$key] = $payload;

        $paymentLog->update([
            'response_data' => $responseData,
        ]);
    }

    private function buildXenditRefundReferenceId(string $orderId): string
    {
        return 'refund_' . $orderId . '_' . now()->format('YmdHis');
    }

    private function buildIdempotencyKey(string $orderId, string $paymentIntentId, int $refundAmountCents): string
    {
        return 'refund_' . $orderId . '_' . $paymentIntentId . '_' . $refundAmountCents;
    }
}
