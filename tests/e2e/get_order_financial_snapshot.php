<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;

$orderId = $argv[1] ?? null;

if (!$orderId) {
    fwrite(STDERR, "Order ID is required.\n");
    exit(1);
}

$order = Order::where('id_order', $orderId)->firstOrFail();
$items = OrderItem::where('id_order', $orderId)->get();

$vendorTotal = (float) $items->sum(function ($item) {
    return (float) ($item->vendor_cost_total ?? 0);
});

if ($vendorTotal <= 0) {
    $vendorTotal = (float) $items->sum(function ($item) {
        return (float) ($item->boat_cost_total ?? 0)
            + (float) ($item->homestay_cost_total ?? 0)
            + (float) ($item->culinary_cost_total ?? 0)
            + (float) ($item->kiosk_cost_total ?? 0);
    });
}

$originalProfit = (float) $items->sum(function ($item) {
    return (float) ($item->company_profit_total ?? ((float) ($item->subtotal ?? 0) - (float) ($item->vendor_cost_total ?? 0)));
});

if ($originalProfit == 0.0) {
    $originalProfit = (float) ($order->base_amount ?? $order->total_amount ?? 0) - $vendorTotal;
}

$reportedProfitImpact = $order->status === 'refunded'
    ? (float) ($order->refund_fee ?? 0)
    : $originalProfit;

echo json_encode([
    'order_id' => $order->id_order,
    'status' => $order->status,
    'base_amount' => (float) ($order->base_amount ?? 0),
    'refund_fee' => (float) ($order->refund_fee ?? 0),
    'refund_amount' => (float) ($order->refund_amount ?? 0),
    'vendor_total' => $vendorTotal,
    'original_profit' => $originalProfit,
    'reported_profit_impact' => $reportedProfitImpact,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;