<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly PaymentGatewayService $paymentGatewayService,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly OrderItemSnapshotService $orderItemSnapshotService
    ) {
    }

    public function getCartItemsForUser(int $userId): Collection
    {
        $cartItems = Cart::with('paket')
            ->where('user_id', $userId)
            ->get();

        return $this->synchronizeCartPrices($cartItems);
    }

    public function createOrderFromCart(array $validated, int $userId, array $context = []): array
    {
        $response = DB::transaction(function () use ($validated, $userId) {
            $cartItems = Cart::with('paket')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            $cartItems = $this->synchronizeCartPrices($cartItems);

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
            $cancelledOrderIds = [];

            $created = false;

            if ($order && !empty($order->payment_method) && $order->payment_method !== $validated['payment_method']) {
                if (!$this->cancelPendingOrderModel($order)) {
                    throw new InvalidArgumentException('Your previous payment is still being processed. Please wait a moment before switching payment methods.');
                }

                $cancelledOrderIds[] = $order->id_order;
                $order = null;
            }

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
                'cancelled_order_ids' => $cancelledOrderIds,
                'stale_pending_order_ids' => $pendingOrders
                    ->skip(1)
                    ->reject(fn ($pendingOrder) => in_array($pendingOrder->id_order, $cancelledOrderIds, true))
                    ->pluck('id_order')
                    ->values()
                    ->all(),
            ];

            $paymentPayload = $this->paymentGatewayService->createPayment($order, [
                'user_id' => $userId,
                'base_amount' => $baseAmount,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'display_currency' => $displayCurrency,
                'display_amount' => $displayAmount,
            ]);

            $response = array_merge($response, $paymentPayload);

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
        Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('payment_method', 'xendit')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function (Order $order) {
                $this->paymentGatewayService->syncPendingOrder($order);
            });

        $query = Order::with([
            'items' => fn ($itemQuery) => $itemQuery->select([
                'id',
                'id_order',
                'nama_paket',
                'jumlah_peserta',
                'tanggal_keberangkatan',
                'subtotal',
            ]),
        ])
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

            $originalSubtotal = (float) ($item->paket->harga_jual ?? $item->subtotal ?? 0);
            $netSubtotal = (float) ($item->subtotal ?? 0);
            $discountAmount = max(0, $originalSubtotal - $netSubtotal);
            $discountPercentage = $originalSubtotal > 0
                ? round(($discountAmount / $originalSubtotal) * 100, 2)
                : 0.0;
            $discountType = $discountAmount > 0
                ? (string) ($item->paket->tipe_diskon ?? 'none')
                : 'none';

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
                'original_subtotal' => $originalSubtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'discount_type' => $discountType,
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

        if (!$this->paymentGatewayService->cancelPendingPayment($order)) {
            return false;
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

    private function synchronizeCartPrices(Collection $cartItems): Collection
    {
        foreach ($cartItems as $cartItem) {
            $latestPrice = (float) ($cartItem->paket->harga_final ?? $cartItem->harga_satuan ?? 0);

            if (
                round((float) ($cartItem->harga_satuan ?? 0), 2) !== round($latestPrice, 2)
                || round((float) ($cartItem->subtotal ?? 0), 2) !== round($latestPrice, 2)
            ) {
                $cartItem->harga_satuan = $latestPrice;
                $cartItem->subtotal = $latestPrice;
                $cartItem->save();
            }
        }

        return $cartItems;
    }
}
