<?php

namespace App\Services;

use App\Events\AdminRealtimeNotification;
use App\Models\AdminNotification;
use App\Models\Cart;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class AdminNotificationService
{
    public function notifyNewOrder(Order $order, array $context = []): void
    {
        $this->dispatch(
            $this->buildOrderPayload(
                $order,
                'new_order',
                'New booking received',
                'A customer has placed a new booking.'
            ),
            $context
        );
    }

    public function notifyPaymentPaid(Order $order, array $context = []): void
    {
        $this->dispatch(
            $this->buildOrderPayload(
                $order,
                'payment_paid',
                'Payment confirmed',
                'The booking payment has been completed successfully.'
            ),
            $context
        );
    }

    public function notifyRefundRequested(Order $order, array $context = []): void
    {
        $payload = $this->buildOrderPayload(
            $order,
            'refund_requested',
            'Refund request received',
            'A customer has requested a refund for this booking.'
        );

        $payload['refund_reason'] = (string) ($order->refund_reason ?? '');

        $this->dispatch($payload, $context);
    }

    public function notifyRefundProcessed(Order $order, array $context = []): void
    {
        $payload = $this->buildOrderPayload(
            $order,
            'refund_processed',
            'Refund processed',
            'The customer refund has been processed successfully.'
        );

        $payload['refund_amount'] = (float) ($order->refund_amount ?? 0);
        $payload['refund_fee'] = (float) ($order->refund_fee ?? 0);

        $this->dispatch($payload, $context);
    }

    public function notifyCartAdded(Cart $cart, array $context = []): void
    {
        $cart->loadMissing('paket', 'user');

        $payload = [
            'id' => (string) Str::uuid(),
            'type' => 'cart_added',
            'title' => 'Package added to cart',
            'message' => 'A customer added a package to their cart.',
            'order_id' => null,
            'customer_name' => (string) ($cart->user->name ?? 'Guest'),
            'customer_email' => (string) ($cart->user->email ?? ''),
            'package_names' => [(string) ($cart->paket->nama_paket ?? 'Unknown Package')],
            'total_amount' => (float) ($cart->subtotal ?? 0),
            'currency' => 'MYR',
            'total_people' => (int) ($cart->jumlah_peserta ?? 0),
            'origin' => $this->resolveOrigin($context['origin'] ?? null),
            'source_ip' => (string) ($context['source_ip'] ?? ''),
            'status' => 'in_cart',
            'created_at' => now()->toIso8601String(),
        ];

        $this->dispatch($payload, $context);
    }

    private function buildOrderPayload(Order $order, string $type, string $title, string $message): array
    {
        $order->loadMissing('items');
        $packages = $order->items->pluck('nama_paket')->filter()->unique()->values()->all();

        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'order_id' => (string) $order->id_order,
            'customer_name' => (string) ($order->customer_name ?? ''),
            'customer_email' => (string) ($order->customer_email ?? ''),
            'package_names' => array_values($packages),
            'total_amount' => (float) ($order->base_amount ?? 0),
            'currency' => 'MYR',
            'total_people' => (int) $order->items->sum(function ($item) {
                return (int) ($item->jumlah_peserta ?? 0);
            }),
            'origin' => $this->resolveOrigin((string) ($order->customer_address ?? '')),
            'source_ip' => '',
            'status' => (string) ($order->status ?? ''),
            'created_at' => now()->toIso8601String(),
        ];
    }

    private function dispatch(array $payload, array $context = []): void
    {
        try {
            if (!empty($context['source_ip'])) {
                $payload['source_ip'] = (string) $context['source_ip'];
            }

            if (!empty($context['origin'])) {
                $payload['origin'] = $this->resolveOrigin((string) $context['origin']);
            }

            $notification = $this->store($payload);
            event(new AdminRealtimeNotification($notification->toPayload()));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function store(array $payload): AdminNotification
    {
        $knownKeys = [
            'id',
            'type',
            'title',
            'message',
            'order_id',
            'customer_name',
            'customer_email',
            'package_names',
            'total_amount',
            'currency',
            'total_people',
            'origin',
            'source_ip',
            'status',
            'created_at',
        ];

        return AdminNotification::create([
            'event_uuid' => (string) ($payload['id'] ?? Str::uuid()),
            'type' => (string) ($payload['type'] ?? 'general'),
            'title' => (string) ($payload['title'] ?? 'Notification'),
            'message' => (string) ($payload['message'] ?? ''),
            'order_id' => isset($payload['order_id']) ? (string) $payload['order_id'] : null,
            'customer_name' => (string) ($payload['customer_name'] ?? ''),
            'customer_email' => (string) ($payload['customer_email'] ?? ''),
            'package_names' => array_values($payload['package_names'] ?? []),
            'total_amount' => (float) ($payload['total_amount'] ?? 0),
            'currency' => (string) ($payload['currency'] ?? 'MYR'),
            'total_people' => (int) ($payload['total_people'] ?? 0),
            'origin' => (string) ($payload['origin'] ?? ''),
            'source_ip' => (string) ($payload['source_ip'] ?? ''),
            'status' => (string) ($payload['status'] ?? ''),
            'meta' => Arr::except($payload, $knownKeys),
            'event_created_at' => !empty($payload['created_at']) ? Carbon::parse((string) $payload['created_at']) : now(),
        ]);
    }

    private function resolveOrigin(?string $origin): string
    {
        $clean = trim((string) $origin);
        if ($clean === '') {
            return 'Unknown';
        }

        return Str::limit($clean, 120);
    }
}
