<?php

namespace App\Services;

use App\Helpers\StripePaymentHelper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly StripePaymentHelper $stripePaymentHelper,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly OrderItemSnapshotService $orderItemSnapshotService
    ) {
    }

    public function getCartItemsForUser(int $userId): Collection
    {
        return Cart::with('paket')
            ->where('user_id', $userId)
            ->get();
    }

    public function createOrderFromCart(array $validated, int $userId, array $context = []): array
    {
        $response = DB::transaction(function () use ($validated, $userId) {
            $cartItems = Cart::with('paket')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw new InvalidArgumentException('Your cart is empty.');
            }

            $baseAmount = (float) $cartItems->sum('subtotal');
            $displayCurrency = $validated['display_currency'] ?? 'MYR';
            $displayAmount = null;
            $displayRate = 1.0;

            if ($displayCurrency !== 'MYR') {
                $displayRate = $this->exchangeRateService->getRate($displayCurrency);
                $displayAmount = $this->exchangeRateService->convert($baseAmount, $displayCurrency);
            }

            $cartSignature = $this->buildCartSignature($cartItems);

            $pendingOrders = Order::where('user_id', $userId)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->get();

            $order = $pendingOrders->first();

            $created = false;

            if (!$order) {
                $order = Order::create([
                    'user_id' => $userId,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'] ?? null,
                    'cart_signature' => $cartSignature,
                    'base_amount' => $baseAmount,
                    'total_amount' => $baseAmount,
                    'display_currency' => $displayCurrency,
                    'display_amount' => $displayAmount,
                    'display_exchange_rate' => $displayRate,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'pending',
                ]);

                $this->syncOrderItemsFromCart($order, $cartItems);

                $created = true;
            } else {
                $itemsNeedSync = $order->cart_signature !== $cartSignature || !$order->items()->exists();

                $order->update([
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'] ?? null,
                    'cart_signature' => $cartSignature,
                    'base_amount' => $baseAmount,
                    'total_amount' => $baseAmount,
                    'display_currency' => $displayCurrency,
                    'display_amount' => $displayAmount,
                    'display_exchange_rate' => $displayRate,
                    'payment_method' => $validated['payment_method'],
                ]);

                if ($itemsNeedSync) {
                    $this->syncOrderItemsFromCart($order, $cartItems);
                }
            }

            $response = [
                'order_id' => $order->id_order,
                'client_secret' => null,
                'created' => $created,
                'stale_pending_order_ids' => $pendingOrders
                    ->skip(1)
                    ->pluck('id_order')
                    ->values()
                    ->all(),
            ];

            if ($validated['payment_method'] === 'stripe') {
                $response['client_secret'] = $this->resolveStripeClientSecret(
                    $order,
                    $userId,
                    $baseAmount,
                    $validated['customer_name'],
                    $validated['customer_email'],
                    $displayCurrency,
                    $displayAmount
                );
            }

            return $response;
        });

        $order = Order::with('items')->where('id_order', $response['order_id'])->first();
        $this->cancelStalePendingOrders($response['stale_pending_order_ids'] ?? []);

        if (($response['created'] ?? false) && $order) {
            $this->adminNotificationService->notifyNewOrder($order, $context);
        }

        return $response;
    }

    public function getOrderStatusForUser(string $orderId, int $userId): ?Order
    {
        return Order::where('id_order', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getOrderForUser(string $orderId, int $userId): Order
    {
        return Order::where('id_order', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function getOrderWithItemsForUser(string $orderId, int $userId): Order
    {
        return Order::with('items.paket')
            ->where('id_order', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function getHistoryForUser(int $userId, ?string $status): array
    {
        $query = Order::with('items.paket')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['pending', 'paid', 'failed', 'cancelled', 'refund_requested', 'refunded'], true)) {
            if ($status === 'failed') {
                $query->whereIn('status', ['failed', 'cancelled']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(10);

        $statsRow = Order::where('user_id', $userId)
            ->selectRaw(
                "COUNT(*) as total,
                 SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = 'refund_requested' THEN 1 ELSE 0 END) as refund_requested,
                 SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded,
                 SUM(CASE WHEN status IN ('failed', 'cancelled') THEN 1 ELSE 0 END) as failed"
            )
            ->first();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'paid' => (int) ($statsRow->paid ?? 0),
            'pending' => (int) ($statsRow->pending ?? 0),
            'refund_requested' => (int) ($statsRow->refund_requested ?? 0),
            'refunded' => (int) ($statsRow->refunded ?? 0),
            'failed' => (int) ($statsRow->failed ?? 0),
        ];

        return [
            'orders' => $orders,
            'stats' => $stats,
            'status' => $status,
        ];
    }

    public function cancelPendingOrder(string $orderId, int $userId): bool
    {
        $order = Order::where('id_order', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $this->cancelPendingOrderModel($order);
    }

    public function requestRefund(string $orderId, int $userId, string $reason, array $context = []): bool
    {
        $order = Order::where('id_order', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->status !== 'paid') {
            return false;
        }

        $order->update([
            'status' => 'refund_requested',
            'refund_reason' => $reason,
            'refund_status' => 'requested',
            'refund_rejected_reason' => null,
            'refund_amount' => null,
            'refund_fee' => null,
            'stripe_refund_id' => null,
            'refunded_at' => null,
            'refund_failure_reason' => null,
        ]);

        $this->adminNotificationService->notifyRefundRequested($order->fresh('items'), $context);

        return true;
    }

    private function buildCartSignature(Collection $cartItems): string
    {
        $signaturePayload = $cartItems
            ->sortBy(fn ($item) => implode('|', [
                (string) $item->id_paket,
                (string) $item->tanggal_keberangkatan,
                (string) $item->jumlah_peserta,
            ]))
            ->values()
            ->map(function ($item) {
                return [
                    'id_paket' => (string) $item->id_paket,
                    'jumlah_peserta' => (int) $item->jumlah_peserta,
                    'tanggal_keberangkatan' => optional($item->tanggal_keberangkatan)->format('Y-m-d'),
                    'catatan' => trim((string) ($item->catatan ?? '')),
                    'harga_satuan' => number_format((float) $item->harga_satuan, 2, '.', ''),
                    'subtotal' => number_format((float) $item->subtotal, 2, '.', ''),
                ];
            })
            ->all();

        return hash('sha256', json_encode($signaturePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function syncOrderItemsFromCart(Order $order, Collection $cartItems): void
    {
        $order->items()->delete();

        foreach ($cartItems as $item) {
            $snapshot = $this->orderItemSnapshotService->buildPackageSnapshot(
                (string) $item->id_paket,
                (float) $item->subtotal
            );

            OrderItem::create([
                'id_order' => $order->id_order,
                'id_paket' => $item->id_paket,
                'nama_paket' => $item->paket->nama_paket,
                'durasi_hari' => $item->paket->durasi_hari,
                'jumlah_peserta' => $item->jumlah_peserta,
                'tanggal_keberangkatan' => $item->tanggal_keberangkatan,
                'catatan' => $item->catatan,
                'harga_satuan' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
                'boat_cost_total' => $snapshot['boat_total'],
                'homestay_cost_total' => $snapshot['homestay_total'],
                'culinary_cost_total' => $snapshot['culinary_total'],
                'kiosk_cost_total' => $snapshot['kiosk_total'],
                'vendor_cost_total' => $snapshot['vendor_total'],
                'company_profit_total' => $snapshot['company_profit'],
                'boat_cost_items' => $snapshot['boat_items'],
                'homestay_cost_items' => $snapshot['homestay_items'],
                'culinary_cost_items' => $snapshot['culinary_items'],
                'kiosk_cost_items' => $snapshot['kiosk_items'],
            ]);
        }
    }

    private function cancelStalePendingOrders(array $orderIds): void
    {
        if ($orderIds === []) {
            return;
        }

        $staleOrders = Order::whereIn('id_order', $orderIds)
            ->where('status', 'pending')
            ->get();

        foreach ($staleOrders as $staleOrder) {
            $this->cancelPendingOrderModel($staleOrder);
        }
    }

    private function cancelPendingOrderModel(Order $order): bool
    {
        if ($order->status !== 'pending') {
            return false;
        }

        if (!empty($order->payment_intent_id)) {
            try {
                $paymentIntent = $this->stripePaymentHelper->retrievePaymentIntent($order->payment_intent_id);

                if ($paymentIntent->status === 'succeeded') {
                    return false;
                }

                if ($paymentIntent->status !== 'canceled') {
                    $paymentIntent = $this->stripePaymentHelper->cancelPaymentIntent($order->payment_intent_id);
                }

                if (($paymentIntent->status ?? null) === 'succeeded') {
                    return false;
                }
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

                    if ($latestPaymentIntent->status !== 'canceled') {
                        return false;
                    }
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

        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::where('id_order', $order->id_order)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder || $lockedOrder->status !== 'pending') {
                return false;
            }

            $lockedOrder->update(['status' => 'cancelled']);

            if (!empty($lockedOrder->payment_intent_id)) {
                PaymentLog::where('payment_intent_id', $lockedOrder->payment_intent_id)->update([
                    'status' => 'failed',
                ]);
            }

            return true;
        });
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

                if (in_array($existingIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'], true)
                    && $amountMatches
                    && !empty($existingIntent->client_secret)) {
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
}
