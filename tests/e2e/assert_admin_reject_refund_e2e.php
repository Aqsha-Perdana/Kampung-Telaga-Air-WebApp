<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;

$email = 'playwright.booking.e2e@example.com';
$expectedRejectionReason = 'Booking remains valid because the package date is too close to departure.';

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
    'status_paid' => $order->status === 'paid',
    'refund_status_rejected' => $order->refund_status === 'rejected',
    'refund_rejected_reason' => $order->refund_rejected_reason === $expectedRejectionReason,
    'refund_failure_reason_cleared' => empty($order->refund_failure_reason),
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
    'refund_rejected_reason' => $order->refund_rejected_reason,
    'checks' => $checks,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($ok ? 0 : 1);
