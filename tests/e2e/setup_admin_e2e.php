<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

$email = 'playwright.admin.e2e@example.com';
$password = 'AdminE2E123!';

$admin = Admin::updateOrCreate(
    ['email' => $email],
    [
        'name' => 'Playwright Admin E2E',
        'password' => Hash::make($password),
        'phone' => '08123456780',
        'role' => 'admin',
        'is_active' => true,
    ]
);

echo json_encode([
    'email' => $email,
    'password' => $password,
    'admin_id' => $admin->id,
], JSON_UNESCAPED_SLASHES) . PHP_EOL;
