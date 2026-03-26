<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getKPIMetrics()
    {
        $now = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Revenue
        $totalRevenue = DB::table('orders')->where('status', 'paid')->sum('total_amount');
        $revenueThisMonth = DB::table('orders')
            ->where('status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');
        $revenueLastMonth = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$lastMonth, $endOfLastMonth])
            ->sum('total_amount');
        
        $revenueGrowth = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 
            : 0;

        // 2. Orders & Average Value
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

        // 3. Total Orders Counts
        $totalOrders = DB::table('orders')->count();
        $ordersThisMonth = DB::table('orders')->where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth = DB::table('orders')->whereBetween('created_at', [$lastMonth, $endOfLastMonth])->count();
        $ordersGrowth = $ordersLastMonth > 0 
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100 
            : 0;

        // 4. Counts by Status
        $pendingPayments = DB::table('orders')->where('status', 'pending')->count();
        $completedOrders = DB::table('orders')->where('status', 'completed')->count();
        
        $ordersByStatus = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 5. Participants
        $totalParticipants = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->sum('order_items.jumlah_peserta');

        $thisMonthParticipants = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->where('orders.created_at', '>=', $thisMonth)
            ->sum('order_items.jumlah_peserta');

        return compact(
            'totalRevenue', 'revenueThisMonth', 'revenueLastMonth', 'revenueGrowth',
            'avgOrderValue', 'avgOrderValueThisMonth', 'avgOrderGrowth',
            'totalOrders', 'ordersThisMonth', 'ordersGrowth',
            'pendingPayments', 'completedOrders', 'ordersByStatus',
            'totalParticipants', 'thisMonthParticipants'
        );
    }

    public function getRevenueTrend()
    {
        return DB::table('orders')
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
    }

    public function getTopPackages()
    {
        return DB::table('order_items')
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
    }

    public function getRecentOrders($limit = 5)
    {
        return DB::table('orders')
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
            ->paginate($limit);
    }

    public function getResourceStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Operational Expenses
        $operationalExpenses = DB::table('beban_operasionals')
            ->where('tanggal', '>=', $thisMonth)
            ->sum('jumlah');
        
        // Homestay Utilization
        $totalHomestays = DB::table('homestays')->where('is_active', 1)->count();
        $bookedHomestays = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_items.tanggal_keberangkatan', '>=', $today)
            ->distinct('paket_wisata_homestay.id_homestay')
            ->count();
        
        // Boat Utilization
        $totalBoats = DB::table('boats')->where('is_active', 1)->count();
        $bookedBoats = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->where('order_items.tanggal_keberangkatan', '>=', $today)
            ->distinct('paket_wisata_boat.id_boat')
            ->count();

        // Upcoming Departures
        $upcomingDepartures = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('order_items.tanggal_keberangkatan', [
                $today,
                Carbon::today()->addDays(7)
            ])
            ->orderBy('order_items.tanggal_keberangkatan')
            ->get();

        return compact(
            'operationalExpenses', 
            'totalHomestays', 'bookedHomestays', 
            'totalBoats', 'bookedBoats',
            'upcomingDepartures'
        );
    }

    public function getSalesAnalysis()
    {
        $recognizedStatuses = ['paid', 'confirmed', 'completed'];
        $recognizedStatusSql = "'" . implode("','", $recognizedStatuses) . "'";

        // 1. Package Sales
        $packageSales = DB::table('paket_wisatas')
            ->leftJoin('order_items', 'paket_wisatas.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->select(
                'paket_wisatas.id_paket',
                'paket_wisatas.nama_paket',
                'paket_wisatas.deskripsi',
                'paket_wisatas.harga_final',
                'paket_wisatas.durasi_hari',
                'paket_wisatas.status',
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN orders.id_order END) as total_sales"),
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status = 'refunded' THEN orders.id_order END) as refunded_orders"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN order_items.jumlah_peserta ELSE 0 END), 0) as total_participants"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN order_items.subtotal ELSE 0 END), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'refunded' THEN order_items.subtotal ELSE 0 END), 0) as refunded_revenue")
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

        // 2. Boat Sales
        $boatSales = DB::table('boats')
            ->leftJoin('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->leftJoin('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->select(
                'boats.id',
                'boats.id_boat',
                'boats.nama',
                'boats.kapasitas',
                'boats.harga_sewa',
                'boats.is_active',
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN orders.id_order END) as total_sales"),
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status = 'refunded' THEN orders.id_order END) as refunded_orders"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN order_items.jumlah_peserta ELSE 0 END), 0) as total_participants"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN boats.harga_sewa ELSE 0 END), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'refunded' THEN boats.harga_sewa ELSE 0 END), 0) as refunded_revenue")
            )
            ->where('boats.is_active', 1)
            ->groupBy('boats.id', 'boats.id_boat', 'boats.nama', 'boats.kapasitas', 'boats.harga_sewa', 'boats.is_active')
            ->orderBy('total_sales', 'desc')
            ->get();

        // 3. Homestay Sales
        $homestaySales = DB::table('homestays')
            ->leftJoin('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->leftJoin('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->select(
                'homestays.id',
                'homestays.id_homestay',
                'homestays.nama',
                'homestays.kapasitas',
                'homestays.harga_per_malam',
                'homestays.is_active',
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN orders.id_order END) as total_sales"),
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status = 'refunded' THEN orders.id_order END) as refunded_orders"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN paket_wisata_homestay.jumlah_malam ELSE 0 END), 0) as total_nights"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN (homestays.harga_per_malam * COALESCE(paket_wisata_homestay.jumlah_malam, 1)) ELSE 0 END), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'refunded' THEN (homestays.harga_per_malam * COALESCE(paket_wisata_homestay.jumlah_malam, 1)) ELSE 0 END), 0) as refunded_revenue")
            )
            ->where('homestays.is_active', 1)
            ->groupBy('homestays.id', 'homestays.id_homestay', 'homestays.nama', 'homestays.kapasitas', 'homestays.harga_per_malam', 'homestays.is_active')
            ->orderBy('total_sales', 'desc')
            ->get();

        // 4. Culinary Sales
        $culinarySales = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->leftJoin('paket_wisata_culinary', 'paket_culinaries.id', '=', 'paket_wisata_culinary.id_paket_culinary')
            ->leftJoin('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->select(
                'culinaries.id_culinary',
                'culinaries.nama',
                'paket_culinaries.id',
                'paket_culinaries.nama_paket',
                'paket_culinaries.kapasitas',
                'paket_culinaries.harga',
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN orders.id_order END) as total_sales"),
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status = 'refunded' THEN orders.id_order END) as refunded_orders"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN order_items.jumlah_peserta ELSE 0 END), 0) as total_participants"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN paket_culinaries.harga ELSE 0 END), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'refunded' THEN paket_culinaries.harga ELSE 0 END), 0) as refunded_revenue")
            )
            ->groupBy('culinaries.id_culinary', 'culinaries.nama', 'paket_culinaries.id', 'paket_culinaries.nama_paket', 'paket_culinaries.kapasitas', 'paket_culinaries.harga')
            ->orderBy('total_sales', 'desc')
            ->get();

        // 5. Kiosk Sales
        $kioskSales = DB::table('kiosks')
            ->leftJoin('paket_wisata_kiosk', 'kiosks.id_kiosk', '=', 'paket_wisata_kiosk.id_kiosk')
            ->leftJoin('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->leftJoin('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->select(
                'kiosks.id_kiosk',
                'kiosks.nama',
                'kiosks.kapasitas',
                'kiosks.harga_per_paket',
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN orders.id_order END) as total_sales"),
                DB::raw("COUNT(DISTINCT CASE WHEN orders.status = 'refunded' THEN orders.id_order END) as refunded_orders"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN order_items.jumlah_peserta ELSE 0 END), 0) as total_participants"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status IN ({$recognizedStatusSql}) THEN kiosks.harga_per_paket ELSE 0 END), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'refunded' THEN kiosks.harga_per_paket ELSE 0 END), 0) as refunded_revenue")
            )
            ->groupBy('kiosks.id_kiosk', 'kiosks.nama', 'kiosks.kapasitas', 'kiosks.harga_per_paket')
            ->orderBy('total_sales', 'desc')
            ->get();

        return compact('packageSales', 'boatSales', 'homestaySales', 'culinarySales', 'kioskSales');
    }

    public function calculateResourceMetrics($salesData)
    {
        extract($salesData);

        // Unsold
        $unsoldPackages = $packageSales->where('total_sales', 0);
        $unsoldBoats = $boatSales->where('total_sales', 0);
        $unsoldHomestays = $homestaySales->where('total_sales', 0);
        $unsoldCulinary = $culinarySales->where('total_sales', 0);
        $unsoldKiosks = $kioskSales->where('total_sales', 0);

        // Averages
        $avgPackageSales = $packageSales->avg('total_sales');
        $avgBoatSales = $boatSales->avg('total_sales');
        $avgHomestaySales = $homestaySales->avg('total_sales');
        $avgCulinarySales = $culinarySales->avg('total_sales');
        $avgKioskSales = $kioskSales->avg('total_sales');

        // Low Performing
        $lowPerformingPackages = $packageSales->filter(fn($p) => $p->total_sales > 0 && $p->total_sales < $avgPackageSales);
        $lowPerformingBoats = $boatSales->filter(fn($b) => $b->total_sales > 0 && $b->total_sales < $avgBoatSales);
        $lowPerformingHomestays = $homestaySales->filter(fn($h) => $h->total_sales > 0 && $h->total_sales < $avgHomestaySales);
        $lowPerformingCulinary = $culinarySales->filter(fn($c) => $c->total_sales > 0 && $c->total_sales < $avgCulinarySales);
        $lowPerformingKiosks = $kioskSales->filter(fn($k) => $k->total_sales > 0 && $k->total_sales < $avgKioskSales);

        // Metrics Array
        $resourceMetrics = [
            'packages' => $this->getMetric($packageSales, $unsoldPackages, $avgPackageSales),
            'boats' => $this->getMetric($boatSales, $unsoldBoats, $avgBoatSales),
            'homestays' => $this->getMetric($homestaySales, $unsoldHomestays, $avgHomestaySales),
            'culinary' => $this->getMetric($culinarySales, $unsoldCulinary, $avgCulinarySales),
            'kiosks' => $this->getMetric($kioskSales, $unsoldKiosks, $avgKioskSales),
        ];

        return compact(
            'unsoldPackages', 'unsoldBoats', 'unsoldHomestays', 'unsoldCulinary', 'unsoldKiosks',
            'lowPerformingPackages', 'lowPerformingBoats', 'lowPerformingHomestays', 'lowPerformingCulinary', 'lowPerformingKiosks',
            'resourceMetrics'
        );
    }

    private function getMetric($all, $unsold, $avg)
    {
        $total = $all->count();
        $sold = $all->where('total_sales', '>', 0)->count();
        
        return [
            'total' => $total,
            'sold' => $sold,
            'unsold' => $unsold->count(),
            'avg_sales' => round($avg, 2),
            'distribution_score' => $total > 0 ? round(($sold / $total) * 100, 1) : 0
        ];
    }

    /**
     * Get revenue breakdown by category for pie/donut chart
     * Note: Since order_items only contains id_paket (packages), we calculate
     * component revenue by joining through paket_wisata_* pivot tables
     */
    public function getRevenueByCategory()
    {
        $recognizedStatuses = ['paid', 'confirmed', 'completed'];

        // Total package revenue (direct from orders)
        $packageRevenue = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $recognizedStatuses)
            ->whereNotNull('order_items.id_paket')
            ->sum('order_items.subtotal');

        // Boat revenue: orders for packages that include boats
        $boatRevenue = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')
            ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
            ->whereIn('orders.status', $recognizedStatuses)
            ->sum('boats.harga_sewa');

        // Homestay revenue: orders for packages that include homestays
        $homestayRevenue = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')
            ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
            ->whereIn('orders.status', $recognizedStatuses)
            ->selectRaw('SUM(homestays.harga_per_malam * COALESCE(paket_wisata_homestay.jumlah_malam, 1)) as total')
            ->value('total') ?? 0;

        // Culinary revenue: orders for packages that include culinary
        $culinaryRevenue = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->whereIn('orders.status', $recognizedStatuses)
            ->sum('paket_culinaries.harga');

        // Kiosk revenue: orders for packages that include kiosks
        $kioskRevenue = DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')
            ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
            ->whereIn('orders.status', $recognizedStatuses)
            ->sum('kiosks.harga_per_paket');

        return [
            'packages' => $packageRevenue,
            'boats' => $boatRevenue,
            'homestays' => $homestayRevenue,
            'culinary' => $culinaryRevenue,
            'kiosk' => $kioskRevenue,
        ];
    }

    /**
     * Get upcoming bookings for the next 7 days
     */
    public function getUpcomingBookings($days = 7)
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        return DB::table('order_items')
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.customer_phone',
                'order_items.tanggal_keberangkatan',
                'order_items.nama_paket',
                'order_items.jumlah_peserta',
                'orders.status'
            )
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', ['paid', 'confirmed'])
            ->whereBetween('order_items.tanggal_keberangkatan', [$today, $endDate])
            ->orderBy('order_items.tanggal_keberangkatan')
            ->get();
    }

    /**
     * Get items that need attention (pending orders, never-sold resources)
     */
    public function getAttentionItems()
    {
        // Pending orders older than 3 days
        $stalePendingOrders = DB::table('orders')
            ->select('id_order', 'customer_name', 'total_amount', 'created_at')
            ->where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subDays(3))
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        // Count of never-sold resources
        $neverSoldPackages = DB::table('paket_wisatas')
            ->leftJoin('order_items', 'paket_wisatas.id_paket', '=', 'order_items.id_paket')
            ->whereNull('order_items.id_paket')
            ->count();

        $neverSoldBoats = DB::table('boats')
            ->leftJoin('paket_wisata_boat', 'boats.id', '=', 'paket_wisata_boat.id_boat')
            ->leftJoin('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->whereNull('order_items.id_paket')
            ->count();

        $neverSoldHomestays = DB::table('homestays')
            ->leftJoin('paket_wisata_homestay', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
            ->leftJoin('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->whereNull('order_items.id_paket')
            ->count();

        return [
            'stalePendingOrders' => $stalePendingOrders,
            'stalePendingCount' => $stalePendingOrders->count(),
            'neverSoldPackages' => $neverSoldPackages,
            'neverSoldBoats' => $neverSoldBoats,
            'neverSoldHomestays' => $neverSoldHomestays,
        ];
    }
}
