<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;

$email = 'playwright.booking.e2e@example.com';
$orderId = $argv[1] ?? null;

$user = User::where('email', $email)->first();

if (!$user) {
    fwrite(STDERR, "User not found.\n");
    exit(1);
}

$orderQuery = Order::with('items')
    ->where('user_id', $user->id)
    ->orderByDesc('created_at');

if ($orderId) {
    $orderQuery->where('id_order', $orderId);
}

$order = $orderQuery->first();

if (!$order) {
    fwrite(STDERR, "Order not found.\n");
    exit(1);
}

$paymentLog = PaymentLog::where('id_order', $order->id_order)
    ->latest('created_at')
    ->first();

$cartCount = Cart::where('user_id', $user->id)->count();

$checks = [
    'status_cancelled' => $order->status === 'cancelled',
    'payment_method' => $order->payment_method === 'stripe',
    'items_count' => $order->items->count() > 0,
    'payment_log_failed' => $paymentLog?->status === 'failed',
    'cart_still_present' => $cartCount > 0,
];

$ok = !in_array(false, $checks, true);

echo json_encode([
    'ok' => $ok,
    'order_id' => $order->id_order,
    'status' => $order->status,
    'payment_log_status' => $paymentLog?->status,
    'cart_count' => $cartCount,
    'checks' => $checks,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($ok ? 0 : 1);
