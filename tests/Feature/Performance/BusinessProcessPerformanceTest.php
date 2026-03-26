<?php

namespace Tests\Feature\Performance;

use App\Events\AdminRealtimeNotification;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\Culinary;
use App\Models\Destinasi;
use App\Models\Homestay;
use App\Models\Kiosk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaketWisata;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessProcessPerformanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_business_process_performance_audit(): void
    {
        Event::fake([AdminRealtimeNotification::class]);

        $iterations = 5;
        $user = $this->prepareUser();
        $admin = $this->prepareAdmin();
        $paket = $this->preparePaket();

        $this->prepareCart($user->id, $paket->id_paket);
        [$paidOrder, $pendingOrder] = $this->prepareOrders($user->id, $paket->id_paket);

        $destinasi = Destinasi::query()->first();
        $culinary = Culinary::query()->first();
        $kiosk = Kiosk::query()->first();
        $homestay = Homestay::query()->first();

        $scenarios = [];

        $this->logoutAllGuards();
        $scenarios[] = $this->benchmark('Guest - Home', fn () => $this->get('/'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Tour Package', fn () => $this->get('/tour-package'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Package Tour Alt', fn () => $this->get('/package-tour'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Culinary', fn () => $this->get('/culinary'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Kiosk', fn () => $this->get('/kiosk'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Homestay', fn () => $this->get('/homestay'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Destination', fn () => $this->get('/destination'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Cart', fn () => $this->get('/cart'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Visitor Login Page', fn () => $this->get('/visitor/login-visitor'), $iterations);
        $scenarios[] = $this->benchmark('Guest - Admin Login Page', fn () => $this->get('/admin/login'), $iterations);

        if ($destinasi) {
            $scenarios[] = $this->benchmark(
                'Guest - Destination Detail',
                fn () => $this->get('/destination/' . $destinasi->id_destinasi),
                $iterations
            );
        }
        if ($culinary) {
            $scenarios[] = $this->benchmark(
                'Guest - Culinary Detail',
                fn () => $this->get('/culinary/' . $culinary->id_culinary),
                $iterations
            );
        }
        if ($kiosk) {
            $scenarios[] = $this->benchmark(
                'Guest - Kiosk Detail',
                fn () => $this->get('/kiosk/' . $kiosk->id_kiosk),
                $iterations
            );
        }
        if ($homestay) {
            $scenarios[] = $this->benchmark(
                'Guest - Homestay Detail',
                fn () => $this->get('/homestay/' . $homestay->id_homestay),
                $iterations
            );
        }

        $scenarios[] = $this->benchmark(
            'Visitor - Login POST',
            function () use ($user) {
                $this->logoutAllGuards();
                return $this->post('/visitor/login-visitor', [
                    'email' => $user->email,
                    'password' => 'password123',
                ]);
            },
            $iterations
        );

        $scenarios[] = $this->benchmark(
            'Visitor - Checkout Page',
            fn () => $this->actingAs($user)->get('/checkout'),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Order History',
            fn () => $this->actingAs($user)->get('/orders'),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Order Detail',
            fn () => $this->actingAs($user)->get('/orders/' . $paidOrder->id_order),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Invoice Download',
            fn () => $this->actingAs($user)->get('/orders/' . $paidOrder->id_order . '/invoice'),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Payment Success Page',
            fn () => $this->actingAs($user)->get('/checkout/success?order_id=' . $paidOrder->id_order),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Payment Failed Page',
            fn () => $this->actingAs($user)->get('/checkout/failed?order_id=' . $pendingOrder->id_order),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Order Status Polling API',
            fn () => $this->actingAs($user)->get('/api/order-status/' . $pendingOrder->id_order),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Visitor - Cart Count API',
            fn () => $this->actingAs($user)->get('/cart/count'),
            $iterations
        );

        $scenarios[] = $this->benchmark(
            'Admin - Login POST',
            function () use ($admin) {
                $this->logoutAllGuards();
                return $this->post('/admin/login', [
                    'email' => $admin->email,
                    'password' => 'admin12345',
                ]);
            },
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Admin - Dashboard',
            fn () => $this->actingAs($admin, 'admin')->get('/admin/dashboard'),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Admin - Sales Index',
            fn () => $this->actingAs($admin, 'admin')->get('/admin/sales'),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Admin - Sales Detail',
            fn () => $this->actingAs($admin, 'admin')->get('/admin/sales/' . $paidOrder->id_order),
            $iterations
        );
        $scenarios[] = $this->benchmark(
            'Admin - Calendar',
            fn () => $this->actingAs($admin, 'admin')->get('/admin/calendar'),
            $iterations
        );

        $summary = $this->summarize($scenarios);
        $this->persistReport($summary);

        $failed = collect($summary['scenarios'])
            ->filter(fn (array $item) => collect($item['statuses'])->contains(fn (int $status) => $status >= 500))
            ->values()
            ->all();

        $this->assertEmpty(
            $failed,
            'Ada route bisnis yang menghasilkan HTTP 5xx saat benchmark performa. Lihat report JSON untuk detail.'
        );
    }

    private function benchmark(string $name, callable $request, int $iterations): array
    {
        $durations = [];
        $statuses = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $response = $request();
            $durations[] = round((microtime(true) - $start) * 1000, 2);
            $statuses[] = $response->getStatusCode();
        }

        sort($durations);

        return [
            'name' => $name,
            'iterations' => $iterations,
            'avg_ms' => round(array_sum($durations) / max(count($durations), 1), 2),
            'p95_ms' => $this->percentile($durations, 95),
            'p99_ms' => $this->percentile($durations, 99),
            'max_ms' => max($durations),
            'min_ms' => min($durations),
            'statuses' => array_values(array_unique($statuses)),
            'samples_ms' => $durations,
        ];
    }

    private function percentile(array $values, int $percentile): float
    {
        if ($values === []) {
            return 0.0;
        }

        $index = (int) ceil(($percentile / 100) * count($values)) - 1;
        $index = max(0, min($index, count($values) - 1));

        return (float) $values[$index];
    }

    private function summarize(array $scenarios): array
    {
        $avgOfAvg = round(
            collect($scenarios)->avg('avg_ms') ?? 0,
            2
        );

        $slowest = collect($scenarios)
            ->sortByDesc('p95_ms')
            ->take(8)
            ->values()
            ->all();

        return [
            'generated_at' => now()->toIso8601String(),
            'iterations_per_scenario' => $scenarios[0]['iterations'] ?? 0,
            'scenario_count' => count($scenarios),
            'overall_avg_ms' => $avgOfAvg,
            'slowest_by_p95' => $slowest,
            'scenarios' => $scenarios,
        ];
    }

    private function persistReport(array $summary): void
    {
        $path = storage_path('app/performance-business-process.json');
        file_put_contents($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function prepareUser(): User
    {
        $user = User::query()->where('email', 'perf-user@telagaair.test')->first();

        if (!$user) {
            $user = User::query()->create([
                'name' => 'Perf User',
                'email' => 'perf-user@telagaair.test',
                'password' => Hash::make('password123'),
                'phone' => '08123456789',
                'nationality' => 'Indonesia',
                'address' => 'Performance Test Address',
            ]);
        } else {
            $user->password = Hash::make('password123');
            $user->save();
        }

        return $user;
    }

    private function prepareAdmin(): Admin
    {
        $admin = Admin::query()->where('email', 'perf-admin@telagaair.test')->first();

        if (!$admin) {
            $admin = Admin::query()->create([
                'name' => 'Perf Admin',
                'email' => 'perf-admin@telagaair.test',
                'password' => Hash::make('admin12345'),
                'phone' => '08123450000',
                'role' => 'admin',
                'is_active' => true,
            ]);
        } else {
            $admin->password = Hash::make('admin12345');
            $admin->role = 'admin';
            $admin->is_active = true;
            $admin->save();
        }

        return $admin;
    }

    private function preparePaket(): PaketWisata
    {
        $paket = PaketWisata::query()->where('status', 'aktif')->first();

        if ($paket) {
            return $paket;
        }

        return PaketWisata::query()->create([
            'nama_paket' => 'Paket Uji Performa',
            'durasi_hari' => 2,
            'deskripsi' => 'Data uji performa',
            'harga_total' => 100,
            'harga_jual' => 150,
            'diskon_nominal' => 0,
            'diskon_persen' => 0,
            'tipe_diskon' => 'none',
            'harga_final' => 150,
            'status' => 'aktif',
        ]);
    }

    private function prepareCart(int $userId, string $paketId): void
    {
        Cart::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'id_paket' => $paketId,
            ],
            [
                'session_id' => null,
                'jumlah_peserta' => 1,
                'tanggal_keberangkatan' => now()->addDays(5)->toDateString(),
                'catatan' => 'Perf cart item',
                'harga_satuan' => 150,
                'subtotal' => 150,
            ]
        );
    }

    private function prepareOrders(int $userId, string $paketId): array
    {
        $paidOrder = Order::query()->create([
            'user_id' => $userId,
            'customer_name' => 'Perf User',
            'customer_email' => 'perf-user@telagaair.test',
            'customer_phone' => '08123456789',
            'customer_address' => 'Performance Test Address',
            'total_amount' => 150,
            'base_amount' => 150,
            'currency' => 'MYR',
            'display_currency' => 'MYR',
            'display_amount' => 150,
            'display_exchange_rate' => 1,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'paid_at' => now(),
            'redeem_code' => 'KTA-PERF-1001',
        ]);

        OrderItem::query()->create([
            'id_order' => $paidOrder->id_order,
            'id_paket' => $paketId,
            'nama_paket' => 'Paket Uji Performa',
            'durasi_hari' => 2,
            'jumlah_peserta' => 1,
            'tanggal_keberangkatan' => now()->addDays(7)->toDateString(),
            'catatan' => 'Perf order item paid',
            'harga_satuan' => 150,
            'subtotal' => 150,
        ]);

        $pendingOrder = Order::query()->create([
            'user_id' => $userId,
            'customer_name' => 'Perf User',
            'customer_email' => 'perf-user@telagaair.test',
            'customer_phone' => '08123456789',
            'customer_address' => 'Performance Test Address',
            'total_amount' => 150,
            'base_amount' => 150,
            'currency' => 'MYR',
            'display_currency' => 'MYR',
            'display_amount' => 150,
            'display_exchange_rate' => 1,
            'payment_method' => 'stripe',
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'id_order' => $pendingOrder->id_order,
            'id_paket' => $paketId,
            'nama_paket' => 'Paket Uji Performa',
            'durasi_hari' => 2,
            'jumlah_peserta' => 1,
            'tanggal_keberangkatan' => now()->addDays(8)->toDateString(),
            'catatan' => 'Perf order item pending',
            'harga_satuan' => 150,
            'subtotal' => 150,
        ]);

        return [$paidOrder, $pendingOrder];
    }

    private function logoutAllGuards(): void
    {
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        $this->flushSession();
    }
}

