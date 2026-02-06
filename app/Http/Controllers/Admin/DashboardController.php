<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Periode waktu
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // 1. TOTAL REVENUE
        $totalRevenue = DB::table('orders')
            ->where('status', 'paid')
            ->sum('total_amount');
        
        $revenueThisMonth = DB::table('orders')
            ->where('status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');
        
        $revenueLastMonth = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->sum('total_amount');
        
        $revenueGrowth = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 
            : 0;
        // Tambahkan di DashboardController setelah $revenueGrowth
        $paidOrders = DB::table('orders')->where('status', 'paid')->count();
        $avgOrderValue = $paidOrders > 0 ? $totalRevenue / $paidOrders : 0;

        $thisMonthPaidOrders = DB::table('orders')
            ->where('status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->count();
        $avgOrderValueThisMonth = $thisMonthPaidOrders > 0 ? $revenueThisMonth / $thisMonthPaidOrders : 0;

        $avgOrderGrowth = $avgOrderValue > 0 
            ? (($avgOrderValueThisMonth - $avgOrderValue) / $avgOrderValue) * 100 
            : 0;
        
        // 2. TOTAL ORDERS
        $totalOrders = DB::table('orders')->count();
        $ordersThisMonth = DB::table('orders')
            ->where('created_at', '>=', $thisMonth)
            ->count();
        $ordersLastMonth = DB::table('orders')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->count();
        $ordersGrowth = $ordersLastMonth > 0 
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100 
            : 0;
        
        // 3. PENDING PAYMENTS
        $pendingPayments = DB::table('orders')
            ->where('status', 'pending')
            ->count();
        
        // 4. COMPLETED ORDERS
        $completedOrders = DB::table('orders')
            ->where('status', 'completed')
            ->count();
        
        // 5. ORDER STATUS BREAKDOWN
        $ordersByStatus = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // 6. REVENUE TREND (Last 12 Months)
        $revenueTrend = DB::table('orders')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // 7. TOP SELLING PACKAGES
        $topPackages = DB::table('order_items')
            ->select(
                'order_items.nama_paket',
                'order_items.id_paket',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(order_items.jumlah_peserta) as total_participants'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('order_items.id_paket', 'order_items.nama_paket')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get();
        
        // 8. RECENT ORDERS
        // 8. RECENT ORDERS - WITH PACKAGE INFO
        // $recentOrders = DB::table('orders')
        //     ->leftJoin('order_items', 'orders.id_order', '=', 'order_items.id_order')
        //     ->select(
        //         'orders.id_order',
        //         'orders.customer_name',
        //         'orders.total_amount',
        //         'orders.status',
        //         'orders.created_at',
        //         DB::raw('GROUP_CONCAT(DISTINCT order_items.nama_paket SEPARATOR ", ") as packages')
        //     )
        //     ->groupBy('orders.id_order', 'orders.customer_name', 'orders.total_amount', 'orders.status', 'orders.created_at')
        //     ->orderBy('orders.created_at', 'desc')
        //     ->limit(10)
        //     ->get();

        $recentOrders = DB::table('orders')
        ->select(
            'orders.id_order',
            'orders.customer_name',
            'orders.total_amount',
            'orders.status',
            'orders.created_at',
            DB::raw('GROUP_CONCAT(paket_wisatas.nama_paket SEPARATOR ", ") as packages')
        )
        ->leftJoin('order_items', 'orders.id_order', '=', 'order_items.id_order')
        ->leftJoin('paket_wisatas', 'order_items.id_paket', '=', 'paket_wisatas.id_paket')
        ->whereIn('orders.status', ['paid', 'pending', 'confirmed', 'completed', 'cancelled'])
        ->groupBy(
            'orders.id_order',
            'orders.customer_name', 
            'orders.total_amount',
            'orders.status',
            'orders.created_at'
        )
        ->orderBy('orders.created_at', 'desc')
        ->paginate(5); // Menampilkan 10 data per halaman (dapat disesuaikan)
        
        // 9. OPERATIONAL EXPENSES (This Month)
        $operationalExpenses = DB::table('beban_operasionals')
            ->where('tanggal', '>=', $thisMonth)
            ->sum('jumlah');
        
        // 10. HOMESTAY UTILIZATION
        $totalHomestays = DB::table('homestays')->where('is_active', 1)->count();
        $bookedHomestays = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_items.tanggal_keberangkatan', '>=', $today)
            ->distinct('paket_wisata_homestay.id_homestay')
            ->count();
        
        // 11. BOAT UTILIZATION
        $totalBoats = DB::table('boats')->where('is_active', 1)->count();
        $bookedBoats = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_items.tanggal_keberangkatan', '>=', $today)
            ->distinct('paket_wisata_boat.id_boat')
            ->count();
        
        // 12. UPCOMING DEPARTURES (Next 7 Days)
        $upcomingDepartures = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('order_items.tanggal_keberangkatan', [
                $today,
                Carbon::today()->addDays(7)
            ])
            ->orderBy('order_items.tanggal_keberangkatan')
            ->get();
        
        // ============================================
        // TOUR PACKAGES ANALYSIS
        // ============================================
        
        // 13. TOUR PACKAGES SALES DISTRIBUTION
        $packageSales = DB::table('paket_wisatas')
            ->leftJoin('order_items', 'paket_wisatas.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'paket_wisatas.id_paket',
                'paket_wisatas.nama_paket',
                'paket_wisatas.deskripsi',
                'paket_wisatas.harga_final',
                'paket_wisatas.durasi_hari',
                'paket_wisatas.status',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_sales'),
                DB::raw('COALESCE(SUM(order_items.jumlah_peserta), 0) as total_participants'),
                DB::raw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            )
            ->where('paket_wisatas.status', 'aktif')
            ->groupBy(
                'paket_wisatas.id_paket',
                'paket_wisatas.nama_paket',
                'paket_wisatas.deskripsi',
                'paket_wisatas.harga_final',    
                'paket_wisatas.durasi_hari',
                'paket_wisatas.status'
            )
            ->orderBy('total_sales', 'desc')
            ->get();
        
        // ============================================
        // RESOURCE SALES DISTRIBUTION ANALYSIS
        // ============================================
        
        // 14. BOAT SALES DISTRIBUTION
        $boatSales = DB::table('boats')
            ->leftJoin('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->leftJoin('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'boats.id',
                'boats.id_boat',
                'boats.nama',
                'boats.kapasitas',
                'boats.harga_sewa',
                'boats.is_active',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_sales'),
                DB::raw('COALESCE(SUM(order_items.jumlah_peserta), 0) as total_participants'),
                DB::raw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            )
            ->where('boats.is_active', 1)
            ->groupBy('boats.id', 'boats.id_boat', 'boats.nama', 'boats.kapasitas', 'boats.harga_sewa', 'boats.is_active')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function($boat) {
                $boat->total_revenue = $boat->harga_sewa * $boat->total_sales;
                return $boat;
            });
        
        // 15. HOMESTAY SALES DISTRIBUTION
        $homestaySales = DB::table('homestays')
            ->leftJoin('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->leftJoin('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'homestays.id',
                'homestays.id_homestay',
                'homestays.nama',
                'homestays.kapasitas',
                'homestays.harga_per_malam',
                'homestays.is_active',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_sales'),
                DB::raw('COALESCE(SUM(paket_wisata_homestay.jumlah_malam), 0) as total_nights'),
                DB::raw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            )
            ->where('homestays.is_active', 1)
            ->groupBy('homestays.id', 'homestays.id_homestay', 'homestays.nama', 'homestays.kapasitas', 'homestays.harga_per_malam', 'homestays.is_active')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function($homestay) {
                $homestay->total_revenue = $homestay->harga_per_malam * $homestay->total_sales * $homestay->total_nights;
                return $homestay;
            });
        
        // 16. CULINARY SALES DISTRIBUTION
        $culinarySales = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->leftJoin('paket_wisata_culinary', 'paket_culinaries.id', '=', 'paket_wisata_culinary.id_paket_culinary')
            ->leftJoin('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                    ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'culinaries.id_culinary',
                'culinaries.nama',
                'paket_culinaries.id',
                'paket_culinaries.nama_paket',
                'paket_culinaries.kapasitas',
                'paket_culinaries.harga',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_sales'),
                DB::raw('COALESCE(SUM(order_items.jumlah_peserta), 0) as total_participants')
            )
            ->groupBy('culinaries.id_culinary', 'culinaries.nama', 'paket_culinaries.id', 'paket_culinaries.nama_paket', 'paket_culinaries.kapasitas', 'paket_culinaries.harga')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function($culinary) {
                // Calculate revenue as: Price × Sales Count
                $culinary->total_revenue = $culinary->harga * $culinary->total_sales;
                return $culinary;
            });
        
        // 17. KIOSK SALES DISTRIBUTION
        $kioskSales = DB::table('kiosks')
            ->leftJoin('paket_wisata_kiosk', 'kiosks.id_kiosk', '=', 'paket_wisata_kiosk.id_kiosk')
            ->leftJoin('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                     ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'kiosks.id_kiosk',
                'kiosks.nama',
                'kiosks.kapasitas',
                'kiosks.harga_per_paket',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_sales'),
                DB::raw('COALESCE(SUM(order_items.jumlah_peserta), 0) as total_participants'),
                DB::raw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            )
            ->groupBy('kiosks.id_kiosk', 'kiosks.nama', 'kiosks.kapasitas', 'kiosks.harga_per_paket')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function($kiosk) {
                $kiosk->total_revenue = $kiosk->harga_per_paket * $kiosk->total_sales;
                return $kiosk;
            });
        
        // 18. UNSOLD RESOURCES - RECOMMENDATIONS
        $unsoldPackages = $packageSales->where('total_sales', 0);
        $unsoldBoats = $boatSales->where('total_sales', 0);
        $unsoldHomestays = $homestaySales->where('total_sales', 0);
        $unsoldCulinary = $culinarySales->where('total_sales', 0);
        $unsoldKiosks = $kioskSales->where('total_sales', 0);
        
        // 19. LOW PERFORMING RESOURCES (Sold but below average)
        $avgPackageSales = $packageSales->avg('total_sales');
        $avgBoatSales = $boatSales->avg('total_sales');
        $avgHomestaySales = $homestaySales->avg('total_sales');
        $avgCulinarySales = $culinarySales->avg('total_sales');
        $avgKioskSales = $kioskSales->avg('total_sales');
        
        $lowPerformingPackages = $packageSales->filter(function($package) use ($avgPackageSales) {
            return $package->total_sales > 0 && $package->total_sales < $avgPackageSales;
        });
        
        $lowPerformingBoats = $boatSales->filter(function($boat) use ($avgBoatSales) {
            return $boat->total_sales > 0 && $boat->total_sales < $avgBoatSales;
        });
        
        $lowPerformingHomestays = $homestaySales->filter(function($homestay) use ($avgHomestaySales) {
            return $homestay->total_sales > 0 && $homestay->total_sales < $avgHomestaySales;
        });
        
        $lowPerformingCulinary = $culinarySales->filter(function($culinary) use ($avgCulinarySales) {
            return $culinary->total_sales > 0 && $culinary->total_sales < $avgCulinarySales;
        });
        
        $lowPerformingKiosks = $kioskSales->filter(function($kiosk) use ($avgKioskSales) {
            return $kiosk->total_sales > 0 && $kiosk->total_sales < $avgKioskSales;
        });
        
        // 20. SALES DISTRIBUTION METRICS
        $resourceMetrics = [
            'packages' => [
                'total' => $packageSales->count(),
                'sold' => $packageSales->where('total_sales', '>', 0)->count(),
                'unsold' => $unsoldPackages->count(),
                'avg_sales' => round($avgPackageSales, 2),
                'distribution_score' => $packageSales->count() > 0 ? 
                    round(($packageSales->where('total_sales', '>', 0)->count() / $packageSales->count()) * 100, 1) : 0
            ],
            'boats' => [
                'total' => $boatSales->count(),
                'sold' => $boatSales->where('total_sales', '>', 0)->count(),
                'unsold' => $unsoldBoats->count(),
                'avg_sales' => round($avgBoatSales, 2),
                'distribution_score' => $boatSales->count() > 0 ? 
                    round(($boatSales->where('total_sales', '>', 0)->count() / $boatSales->count()) * 100, 1) : 0
            ],
            'homestays' => [
                'total' => $homestaySales->count(),
                'sold' => $homestaySales->where('total_sales', '>', 0)->count(),
                'unsold' => $unsoldHomestays->count(),
                'avg_sales' => round($avgHomestaySales, 2),
                'distribution_score' => $homestaySales->count() > 0 ? 
                    round(($homestaySales->where('total_sales', '>', 0)->count() / $homestaySales->count()) * 100, 1) : 0
            ],
            'culinary' => [
                'total' => $culinarySales->count(),
                'sold' => $culinarySales->where('total_sales', '>', 0)->count(),
                'unsold' => $unsoldCulinary->count(),
                'avg_sales' => round($avgCulinarySales, 2),
                'distribution_score' => $culinarySales->count() > 0 ? 
                    round(($culinarySales->where('total_sales', '>', 0)->count() / $culinarySales->count()) * 100, 1) : 0
            ],
            'kiosks' => [
                'total' => $kioskSales->count(),
                'sold' => $kioskSales->where('total_sales', '>', 0)->count(),
                'unsold' => $unsoldKiosks->count(),
                'avg_sales' => round($avgKioskSales, 2),
                'distribution_score' => $kioskSales->count() > 0 ? 
                    round(($kioskSales->where('total_sales', '>', 0)->count() / $kioskSales->count()) * 100, 1) : 0
            ]
        ];
        
        return view('admin.dashboard', compact(
            'totalRevenue',
            'revenueThisMonth',
            'revenueGrowth',
            'totalOrders',
            'ordersThisMonth',
            'ordersGrowth',
            'pendingPayments',
            'completedOrders',
            'ordersByStatus',
            'revenueTrend',
            'topPackages',
            'recentOrders',
            'operationalExpenses',
            'totalHomestays',
            'bookedHomestays',
            'totalBoats',
            'bookedBoats',
            'upcomingDepartures',
            // Tour Packages Data
            'packageSales',
            'unsoldPackages',
            'lowPerformingPackages',
            // Resource Distribution Data
            'boatSales',
            'homestaySales',
            'culinarySales',
            'kioskSales',
            'unsoldBoats',
            'unsoldHomestays',
            'unsoldCulinary',
            'unsoldKiosks',
            'lowPerformingBoats',
            'avgOrderValue',
            'avgOrderValueThisMonth',
            'avgOrderGrowth',
            'lowPerformingHomestays',
            'lowPerformingCulinary',
            'lowPerformingKiosks',
            'resourceMetrics'
        ));
    }
}