<?php

namespace App\Services;

use App\Helpers\XenditPaymentHelper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class XenditOrderService
{
    public function __construct(
        private readonly XenditPaymentHelper $xenditPaymentHelper,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    public function createPayment(Order $order, array $context = []): array
    {
        $order->loadMissing('items');

        $existingPayment = PaymentLog::query()
            ->where('id_order', $order->id_order)
            ->where('payment_method', 'xendit')
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $existingResponse = $this->normalizeResponseData($existingPayment?->response_data);

        if ($existingPayment && $this->isReusablePendingInvoice($existingResponse)) {
            if (empty($order->payment_intent_id) && !empty($existingResponse['id'])) {
                $order->update(['payment_intent_id' => $existingResponse['id']]);
            }

            return [
                'redirect_url' => (string) $existingResponse['invoice_url'],
                'payment_reference' => (string) ($existingResponse['id'] ?? $order->payment_intent_id),
            ];
        }

        $invoice = $this->xenditPaymentHelper->createInvoice([
            'external_id' => $order->id_order,
            'amount' => round((float) $order->base_amount, 2),
            'description' => 'Telaga Air booking #' . $order->id_order,
            'invoice_duration' => (int) config('payment.methods.xendit.invoice_duration', 3600),
            'currency' => 'MYR',
            'customer' => [
                'given_names' => (string) $order->customer_name,
                'email' => (string) $order->customer_email,
                'mobile_number' => (string) $order->customer_phone,
            ],
            'success_redirect_url' => $this->publicRoute('checkout.success', ['order_id' => $order->id_order]),
            'failure_redirect_url' => $this->publicRoute('checkout.failed', ['order_id' => $order->id_order]),
            'items' => $order->items->map(fn ($item) => [
                'name' => (string) $item->nama_paket,
                'quantity' => 1,
                'price' => round((float) $item->subtotal, 2),
                'category' => 'Tour Package',
            ])->values()->all(),
            'metadata' => [
                'order_id' => $order->id_order,
                'user_id' => (string) ($context['user_id'] ?? $order->user_id ?? ''),
                'display_currency' => (string) ($order->display_currency ?? 'MYR'),
                'display_amount' => (string) ($order->display_amount ?? ''),
            ],
        ]);

        if (empty($invoice['invoice_url'])) {
            throw new \RuntimeException('Xendit did not return an invoice URL.');
        }

        $order->update([
            'payment_intent_id' => $invoice['id'] ?? null,
        ]);

        if ($existingPayment) {
            PaymentLog::query()
                ->where('id_order', $order->id_order)
                ->where('payment_method', 'xendit')
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }

        PaymentLog::create([
            'id_order' => $order->id_order,
            'payment_intent_id' => $invoice['id'] ?? null,
            'payment_method' => 'xendit',
            'amount' => $order->base_amount,
            'currency' => 'MYR',
            'status' => 'pending',
            'response_data' => $invoice,
        ]);

        return [
            'redirect_url' => (string) ($invoice['invoice_url'] ?? ''),
            'payment_reference' => (string) ($invoice['id'] ?? ''),
        ];
    }

    public function handleWebhook(Request $request): void
    {
        $this->xenditPaymentHelper->verifyWebhookToken($request->header('x-callback-token'));

        $payload = $request->json()->all() ?: $request->all();
        $status = strtoupper((string) ($payload['status'] ?? ''));
        $orderId = (string) ($payload['external_id'] ?? '');

        if ($orderId === '') {
            Log::warning('Xendit webhook missing external_id', ['payload' => $payload]);
            return;
        }

        match ($status) {
            'PAID' => $this->handlePaymentSuccess($payload),
            'EXPIRED' => $this->handlePaymentExpired($payload),
            default => Log::info('Ignoring Xendit webhook status', ['order_id' => $orderId, 'status' => $status]),
        };
    }

    public function syncPendingOrder(Order $order): void
    {
        if ($order->status !== 'pending' || $order->payment_method !== 'xendit') {
            return;
        }

        $invoiceId = $this->resolveInvoiceId($order);

        if ($invoiceId === '') {
            return;
        }

        try {
            $invoice = $this->xenditPaymentHelper->getInvoice($invoiceId);
        } catch (\Throwable $e) {
            Log::warning('Unable to sync pending Xendit order.', [
                'order_id' => $order->id_order,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $status = strtoupper((string) ($invoice['status'] ?? ''));

        match ($status) {
            'PAID' => $this->handlePaymentSuccess($invoice),
            'EXPIRED' => $this->handlePaymentExpired($invoice),
            default => null,
        };
    }

    public function cancelPendingPayment(Order $order): bool
    {
        return $order->status === 'pending';
    }

    private function handlePaymentSuccess(array $payload): void
    {
        $notificationsNeeded = false;

        DB::transaction(function () use ($payload, &$notificationsNeeded) {
            $orderId = (string) ($payload['external_id'] ?? '');
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();

            if (!$order) {
                Log::warning('Order not found for Xendit payment success', ['order_id' => $orderId]);
                return;
            }

            if ($order->status === 'cancelled') {
                Log::warning('Ignoring successful Xendit payment for cancelled order', ['order_id' => $orderId]);
                return;
            }

            $paidAt = !empty($payload['paid_at']) ? Carbon::parse($payload['paid_at']) : now();
            $redeemCode = $order->redeem_code ?: $this->generateRedeemCode();
            $reference = (string) ($payload['id'] ?? $payload['payment_id'] ?? $order->payment_intent_id);
            $wasAlreadyPaid = $order->status === 'paid' && !empty($order->redeem_code);
            $paymentMetrics = $this->resolveXenditPaymentMetrics($payload, $order);

            $orderUpdatePayload = [
                'payment_channel' => $paymentMetrics['payment_channel'],
                'payment_intent_id' => $reference,
                'gateway_fee_amount' => $paymentMetrics['fee_amount'],
                'gateway_fee_currency' => $paymentMetrics['fee_currency'],
                'gateway_net_amount' => $paymentMetrics['net_amount'],
                'gateway_fee_source' => $paymentMetrics['fee_source'],
            ];

            if (!$wasAlreadyPaid) {
                $orderUpdatePayload = array_merge($orderUpdatePayload, [
                    'status' => 'paid',
                    'paid_at' => $order->paid_at ?? $paidAt,
                    'redeem_code' => $redeemCode,
                ]);
            }

            $order->update($orderUpdatePayload);

            if (!$wasAlreadyPaid) {
                Cart::where('user_id', $order->user_id)->delete();
                $notificationsNeeded = true;
            }

            $paymentLog = $this->findPendingPaymentLog($orderId, $reference);

            if ($paymentLog) {
                $paymentLog->update([
                    'status' => 'success',
                    'payment_channel' => $paymentMetrics['payment_channel'],
                    'payment_intent_id' => $reference,
                    'fee_amount' => $paymentMetrics['fee_amount'],
                    'fee_currency' => $paymentMetrics['fee_currency'],
                    'net_amount' => $paymentMetrics['net_amount'],
                    'fee_source' => $paymentMetrics['fee_source'],
                    'response_data' => $payload,
                ]);
            } else {
                PaymentLog::create([
                    'id_order' => $orderId,
                    'payment_intent_id' => $reference ?: $order->payment_intent_id,
                    'payment_method' => 'xendit',
                    'payment_channel' => $paymentMetrics['payment_channel'],
                    'amount' => $order->base_amount,
                    'fee_amount' => $paymentMetrics['fee_amount'],
                    'fee_currency' => $paymentMetrics['fee_currency'],
                    'net_amount' => $paymentMetrics['net_amount'],
                    'fee_source' => $paymentMetrics['fee_source'],
                    'currency' => 'MYR',
                    'status' => 'success',
                    'response_data' => $payload,
                ]);
            }

            PaymentLog::query()
                ->where('id_order', $orderId)
                ->where('payment_method', 'xendit')
                ->where('status', 'pending')
                ->when($paymentLog, fn ($query) => $query->where('id', '!=', $paymentLog->id))
                ->update(['status' => 'failed']);
        });

        $orderId = (string) ($payload['external_id'] ?? '');
        $order = Order::with('items')->where('id_order', $orderId)->first();

        if ($notificationsNeeded && $order && $order->status === 'paid') {
            $this->adminNotificationService->notifyPaymentPaid($order);
            $this->customerEmailService->sendOrderPaid($order);
        }
    }

    private function handlePaymentExpired(array $payload): void
    {
        $orderId = (string) ($payload['external_id'] ?? '');
        $reference = (string) ($payload['id'] ?? $payload['payment_id'] ?? '');

        DB::transaction(function () use ($orderId, $payload, $reference) {
            $order = Order::where('id_order', $orderId)->lockForUpdate()->first();

            if (!$order) {
                return;
            }

            $paymentLog = $this->findPendingPaymentLog($orderId, $reference);
            $activePendingLogs = PaymentLog::query()
                ->where('id_order', $orderId)
                ->where('payment_method', 'xendit')
                ->where('status', 'pending');

            if ($paymentLog) {
                $paymentLog->update([
                    'status' => 'failed',
                    'response_data' => $payload,
                ]);

                $activePendingLogs->where('id', '!=', $paymentLog->id);
            }

            if (
                $order->status === 'pending'
                && !$activePendingLogs->exists()
                && ($reference === '' || $order->payment_intent_id === null || $order->payment_intent_id === $reference)
            ) {
                $order->update(['status' => 'failed']);
            }
        });
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

    private function isReusablePendingInvoice(array $responseData): bool
    {
        return strtoupper((string) ($responseData['status'] ?? '')) === 'PENDING'
            && !empty($responseData['invoice_url']);
    }

    private function resolveInvoiceId(Order $order): string
    {
        if (!empty($order->payment_intent_id)) {
            return (string) $order->payment_intent_id;
        }

        $paymentLog = PaymentLog::query()
            ->where('id_order', $order->id_order)
            ->where('payment_method', 'xendit')
            ->orderByDesc('id')
            ->first();

        $responseData = $this->normalizeResponseData($paymentLog?->response_data);

        return (string) ($responseData['id'] ?? '');
    }

    private function findPendingPaymentLog(string $orderId, string $reference = ''): ?PaymentLog
    {
        $baseQuery = PaymentLog::query()
            ->where('id_order', $orderId)
            ->where('payment_method', 'xendit')
            ->where('status', 'pending')
            ->orderByDesc('id');

        if ($reference !== '') {
            $matchedPayment = (clone $baseQuery)
                ->where('payment_intent_id', $reference)
                ->first();

            if ($matchedPayment) {
                return $matchedPayment;
            }
        }

        return $baseQuery->first();
    }

    private function resolveXenditPaymentMetrics(array $payload, Order $order): array
    {
        $grossAmount = $this->toFloat($payload['paid_amount'] ?? $payload['amount'] ?? $order->base_amount) ?? (float) $order->base_amount;
        $netAmount = $this->toFloat($payload['adjusted_received_amount'] ?? null);
        $feeAmount = null;
        $feeSource = 'estimated';

        if ($netAmount !== null) {
            $feeAmount = max(0, $grossAmount - $netAmount);
            $feeSource = 'actual';
        } else {
            $feesPaidAmount = $this->toFloat($payload['fees_paid_amount'] ?? null);
            if ($feesPaidAmount !== null) {
                $feeAmount = max(0, $feesPaidAmount);
                $netAmount = max(0, $grossAmount - $feeAmount);
                $feeSource = 'actual';
            } else {
                [$feeAmount, $netAmount] = $this->estimateXenditFeeFromChannel($payload, $grossAmount);
            }
        }

        $normalizedGatewayAmounts = resolve_gateway_amounts($grossAmount, $feeAmount, $netAmount);

        return [
            'fee_amount' => $normalizedGatewayAmounts['fee_amount'],
            'fee_currency' => strtoupper((string) ($payload['currency'] ?? 'MYR')),
            'net_amount' => $normalizedGatewayAmounts['net_amount'],
            'payment_channel' => (string) ($payload['payment_channel'] ?? $payload['payment_method'] ?? $payload['bank_code'] ?? 'xendit'),
            'fee_source' => $feeSource,
        ];
    }

    private function estimateXenditFeeFromChannel(array $payload, float $grossAmount): array
    {
        $channel = strtoupper((string) ($payload['payment_channel'] ?? $payload['payment_method'] ?? ''));
        $rules = config('payment.methods.xendit.reporting_fee_rules.channels.' . $channel)
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

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function generateRedeemCode(): string
    {
        return 'KTA-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);
    }

    private function publicRoute(string $routeName, array $parameters = []): string
    {
        $baseUrl = rtrim((string) config('services.xendit.public_url', config('app.url')), '/');

        return $baseUrl . route($routeName, $parameters, false);
    }
}
