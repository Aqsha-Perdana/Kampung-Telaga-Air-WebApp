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
    exit(0);
}

$orderIds = Order::where('user_id', $user->id)->pluck('id_order');

if ($orderIds->isNotEmpty()) {
    PaymentLog::whereIn('id_order', $orderIds)->delete();
    \App\Models\OrderItem::whereIn('id_order', $orderIds)->delete();
    Order::whereIn('id_order', $orderIds)->delete();
}

Cart::where('user_id', $user->id)->delete();
