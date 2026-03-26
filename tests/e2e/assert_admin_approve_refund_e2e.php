<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;

$email = 'playwright.booking.e2e@example.com';

$user = User::where('email', $email)->first();

if (!$user) {
    fwrite(STDERR, "User not found.\n");
    exit(1);
}

$order = Order::with('items')
    ->where('user_id', $user->id)
    ->orderByDesc('created_at')
    ->first();

if (!$order) {
    fwrite(STDERR, "Order not found.\n");
    exit(1);
}

$paymentLog = PaymentLog::where('id_order', $order->id_order)
    ->latest('created_at')
    ->first();

$checks = [
    'status_refunded' => $order->status === 'refunded',
    'refund_status_succeeded' => $order->refund_status === 'succeeded',
    'refund_amount' => (float) $order->refund_amount === round(((float) $order->base_amount) * 0.90, 2),
    'refund_fee' => (float) $order->refund_fee === round(((float) $order->base_amount) * 0.10, 2),
    'stripe_refund_id' => !empty($order->stripe_refund_id),
    'refunded_at' => !empty($order->refunded_at),
    'payment_log_success' => $paymentLog?->status === 'success',
    'cart_empty' => Cart::where('user_id', $user->id)->count() === 0,
    'items_count' => $order->items->count() > 0,
];

$ok = !in_array(false, $checks, true);

echo json_encode([
    'ok' => $ok,
    'order_id' => $order->id_order,
    'status' => $order->status,
    'refund_status' => $order->refund_status,
    'refund_amount' => $order->refund_amount,
    'refund_fee' => $order->refund_fee,
    'stripe_refund_id' => $order->stripe_refund_id,
    'checks' => $checks,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($ok ? 0 : 1);
