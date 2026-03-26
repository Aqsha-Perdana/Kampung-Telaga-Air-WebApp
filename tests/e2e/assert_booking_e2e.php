<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;

$email = 'playwright.booking.e2e@example.com';
$user = User::where('email', $email)->first();

if (!$user) {
    fwrite(STDERR, "User not found.\n");
    exit(1);
}

$order = Order::with('items')
    ->where('user_id', $user->id)
    ->latest('created_at')
    ->first();

if (!$order) {
    fwrite(STDERR, "Order not created.\n");
    exit(1);
}

$cartCount = Cart::where('user_id', $user->id)->count();

$checks = [
    'status_paid' => $order->status === 'paid',
    'redeem_code' => !empty($order->redeem_code),
    'payment_method' => $order->payment_method === 'stripe',
    'items_count' => $order->items->count() > 0,
    'cart_empty' => $cartCount === 0,
];

$ok = !in_array(false, $checks, true);

echo json_encode([
    'ok' => $ok,
    'order_id' => $order->id_order,
    'status' => $order->status,
    'redeem_code' => $order->redeem_code,
    'cart_count' => $cartCount,
    'checks' => $checks,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($ok ? 0 : 1);
