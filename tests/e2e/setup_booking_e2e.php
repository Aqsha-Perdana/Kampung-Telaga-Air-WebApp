<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'playwright.booking.e2e@example.com';
$password = 'E2Epassword123!';

$user = User::updateOrCreate(
    ['email' => $email],
    [
        'name' => 'Playwright Booking E2E',
        'password' => Hash::make($password),
        'phone' => '08123456789',
        'nationality' => 'Malaysia',
        'address' => 'Playwright Test Address',
    ]
);

$orderIds = Order::where('user_id', $user->id)->pluck('id_order');

if ($orderIds->isNotEmpty()) {
    PaymentLog::whereIn('id_order', $orderIds)->delete();
    \App\Models\OrderItem::whereIn('id_order', $orderIds)->delete();
    Order::whereIn('id_order', $orderIds)->delete();
}

Cart::where('user_id', $user->id)->delete();

echo json_encode([
    'email' => $email,
    'password' => $password,
    'user_id' => $user->id,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;
