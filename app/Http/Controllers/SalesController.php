<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status', 'all');
        $displayCurrency = $request->input('display_currency', 'all'); // UPDATED: display_currency filter

        // Build base query for orders with items
        $query = DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
            ->join('paket_wisatas', 'order_items.id_paket', '=', 'paket_wisatas.id_paket')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter by status if not 'all'
        if ($status !== 'all') {
            $query->where('orders.status', $status);
        }

        // UPDATED: Filter by display_currency if not 'all'
        if ($displayCurrency !== 'all') {
            if ($displayCurrency === 'MYR') {
                // MYR: either display_currency is NULL or 'MYR'
                $query->where(function($q) {
                    $q->whereNull('orders.display_currency')
                      ->orWhere('orders.display_currency', 'MYR');
                });
            } else {
                $query->where('orders.display_currency', $displayCurrency);
            }
        }

        // Get all order items with details
        $orderItems = $query->select(
            'orders.id_order',
            'orders.customer_name',
            'orders.base_amount',           // ✅ PAYMENT AMOUNT (MYR)
            'orders.payment_amount',        // ✅ PAYMENT AMOUNT (MYR)
            'orders.payment_currency',      // ✅ ALWAYS MYR
            'orders.display_currency',      // ✅ Display only
            'orders.display_amount',        // ✅ Display only
            'orders.display_exchange_rate', // ✅ Display only
            'orders.status',
            'orders.created_at',
            'order_items.id',
            'order_items.id_paket',
            'order_items.nama_paket',
            'order_items.durasi_hari',
            'order_items.harga_satuan',
            'order_items.jumlah_peserta',
            'paket_wisatas.durasi_hari as paket_durasi'
        )->orderBy('orders.created_at', 'desc')->get();

        // Group by orders and calculate breakdown
        $groupedOrders = $orderItems->groupBy('id_order')->map(function($items) {
            $firstItem = $items->first();
            $totalBreakdown = [
                'boat_total' => 0,
                'homestay_total' => 0,
                'culinary_total' => 0,
                'kiosk_total' => 0,
            ];

            // Hitung akumulasi biaya vendor untuk semua item dalam satu order
            foreach ($items as $item) {
                $breakdown = $this->calculateRevenueBreakdown($item->id_paket, $item->durasi_hari ?? $item->paket_durasi);
                
                // Penting: Kalikan biaya per pax dengan jumlah peserta
                $totalBreakdown['boat_total'] += ($breakdown['boat_total'] ?? 0); //* $item->jumlah_peserta//;
                $totalBreakdown['homestay_total'] += ($breakdown['homestay_total'] ?? 0); //* $item->jumlah_peserta//;
                $totalBreakdown['culinary_total'] += ($breakdown['culinary_total'] ?? 0); //* $item->jumlah_peserta//;
                $totalBreakdown['kiosk_total'] += ($breakdown['kiosk_total'] ?? 0); //* $item->jumlah_peserta//;
            }

            // Hitung profit: Base Amount (Apa yang dibayar tamu) - Total Biaya Vendor
            $totalVendorCosts = array_sum($totalBreakdown);
            $companyProfit = $firstItem->base_amount - $totalVendorCosts;

            return (object)[
                'id_order' => $firstItem->id_order,
                'customer_name' => $firstItem->customer_name,
                'base_amount' => $firstItem->base_amount,
                'payment_amount' => $firstItem->payment_amount,
                'payment_currency' => $firstItem->payment_currency ?? 'MYR',
                'display_currency' => $firstItem->display_currency,
                'display_amount' => $firstItem->display_amount,
                'status' => $firstItem->status,
                'created_at' => $firstItem->created_at,
                'items' => $items,
                'items_count' => $items->count(),
                'revenue_breakdown' => $totalBreakdown,
                'company_profit' => $companyProfit, // Simpan ke objek untuk dipanggil di tabel
                'package_names' => $items->pluck('nama_paket')->unique()->implode(', ')
            ];
        })->values();

        // Get summary statistics (only paid orders)
        $paidOrders = $groupedOrders->where('status', 'paid');
        
        $companyRevenue = DB::table('order_items')
            ->join('orders', 'orders.id_order', '=', 'order_items.id_order')
            ->join('paket_wisatas', 'order_items.id_paket', '=', 'paket_wisatas.id_paket')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum(DB::raw('paket_wisatas.harga_final - paket_wisatas.harga_total'));

        // UPDATED: Display Currency breakdown calculation
        $displayCurrencyBreakdown = $this->getDisplayCurrencyBreakdown($startDate, $endDate);
        
        $summary = [
            'total_revenue' => $paidOrders->sum('base_amount'), // ✅ ALWAYS MYR
            'total_orders' => $groupedOrders->count(),
            'paid_orders' => $paidOrders->count(),
            'boat_revenue' => $paidOrders->sum('revenue_breakdown.boat_total'),
            'homestay_revenue' => $paidOrders->sum('revenue_breakdown.homestay_total'),
            'culinary_revenue' => $paidOrders->sum('revenue_breakdown.culinary_total'),
            'kiosk_revenue' => $paidOrders->sum('revenue_breakdown.kiosk_total'),
            'company_revenue' => $companyRevenue,
            'display_currency_breakdown' => $displayCurrencyBreakdown, // ✅ UPDATED
        ];

        // Get entity-wise breakdown (only paid orders)
        $entityBreakdown = [
            'boats' => $this->getBoatBreakdown($startDate, $endDate),
            'homestays' => $this->getHomestayBreakdown($startDate, $endDate),
            'culinaries' => $this->getCulinaryBreakdown($startDate, $endDate),
            'kiosks' => $this->getKioskBreakdown($startDate, $endDate),
        ];

        // Get chart data (daily sales)
        $chartData = $this->getDailySalesChart($startDate, $endDate);

        // UPDATED: Get available display currencies for filter
        $availableDisplayCurrencies = DB::table('orders')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('display_currency')
            ->distinct()
            ->get()
            ->pluck('display_currency')
            ->filter()
            ->push('MYR') // Add MYR
            ->unique()
            ->sort()
            ->values();

        return view('admin.sales.index', compact(
            'groupedOrders',
            'summary',
            'entityBreakdown',
            'chartData',
            'startDate',
            'endDate',
            'status',
            'displayCurrency',
            'availableDisplayCurrencies'
        ));
    }

    // UPDATED: Get display currency breakdown
    private function getDisplayCurrencyBreakdown($startDate, $endDate)
    {
        // Get all paid orders
        $orders = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'display_currency',
                'base_amount',
                'display_amount',
                'display_exchange_rate'
            )
            ->get();

        // Group by display_currency
        $grouped = $orders->groupBy(function($order) {
            return $order->display_currency ?: 'MYR';
        });

        $breakdown = collect();

        foreach ($grouped as $currency => $currencyOrders) {
            $breakdown->push((object)[
                'currency' => $currency,
                'total_orders' => $currencyOrders->count(),
                'total_revenue_myr' => $currencyOrders->sum('base_amount'), // ✅ ALWAYS MYR
                'total_display_amount' => $currencyOrders->sum('display_amount'),
                'avg_exchange_rate' => $currencyOrders->where('display_exchange_rate', '>', 0)->avg('display_exchange_rate') ?? 1
            ]);
        }

        // Sort: MYR first, then by revenue
        return $breakdown->sort(function($a, $b) {
            if ($a->currency === 'MYR') return -1;
            if ($b->currency === 'MYR') return 1;
            return $b->total_revenue_myr <=> $a->total_revenue_myr;
        })->values();
    }

    // Helper to get currency symbol
    private function getCurrencySymbol($currency)
    {
        $symbols = [
            'MYR' => 'RM',
            'USD' => '$',
            'SGD' => 'S$',
            'IDR' => 'Rp',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥',
            'THB' => '฿',
            'BND' => 'B$',
        ];

        return $symbols[$currency] ?? $currency . ' ';
    }

    private function calculateRevenueBreakdown($paketId, $durasiHari)
    {
        $breakdown = [
            'boat_total' => 0,
            'boat_items' => [],
            'homestay_total' => 0,
            'homestay_items' => [],
            'culinary_total' => 0,
            'culinary_items' => [],
            'kiosk_total' => 0,
            'kiosk_items' => [],
        ];

        // Calculate Boat Revenue
        $boats = DB::table('paket_wisata_boat')
            ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
            ->where('paket_wisata_boat.id_paket', $paketId)
            ->select('boats.nama', 'boats.harga_sewa', 'paket_wisata_boat.hari_ke')
            ->get();

        foreach ($boats as $boat) {
            $revenue = $boat->harga_sewa * 1;
            $breakdown['boat_total'] += $revenue;
            $breakdown['boat_items'][] = [
                'nama' => $boat->nama,
                'hari_ke' => $boat->hari_ke,
                'harga_per_hari' => $boat->harga_sewa,
                'revenue' => $revenue
            ];
        }

        // Calculate Homestay Revenue
        $homestays = DB::table('paket_wisata_homestay')
        ->join('homestays', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
        ->where('paket_wisata_homestay.id_paket', $paketId)
        ->select('homestays.nama', 'homestays.harga_per_malam', 'paket_wisata_homestay.jumlah_malam')
        ->get();

        foreach ($homestays as $homestay) {
            $revenue = $homestay->harga_per_malam * $homestay->jumlah_malam;
            $breakdown['homestay_total'] += $revenue;
            $breakdown['homestay_items'][] = [
                'nama' => $homestay->nama,
                'jumlah_malam' => $homestay->jumlah_malam,
                'harga_per_malam' => $homestay->harga_per_malam,
                'revenue' => $revenue
            ];
        }

        // Calculate Culinary Revenue
        $culinaries = DB::table('paket_wisata_culinary')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->where('paket_wisata_culinary.id_paket', $paketId)
            ->select(
                'culinaries.nama', 
                'paket_culinaries.harga', 
                'paket_culinaries.kapasitas', 
                'paket_wisata_culinary.hari_ke'
            )
            ->get();

        foreach ($culinaries as $culinary) {
            $revenue = $culinary->harga;
            $breakdown['culinary_total'] += $revenue;
            $breakdown['culinary_items'][] = [
                'nama' => $culinary->nama,
                'hari_ke' => $culinary->hari_ke,
                'kapasitas' => $culinary->kapasitas,
                'harga' => $culinary->harga,
                'revenue' => $revenue
            ];
        }

        // Calculate Kiosk Revenue
        $kiosks = DB::table('paket_wisata_kiosk')
            ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
            ->where('paket_wisata_kiosk.id_paket', $paketId)
            ->select('kiosks.nama', 'kiosks.harga_per_paket', 'paket_wisata_kiosk.hari_ke')
            ->get();

        foreach ($kiosks as $kiosk) {
            $revenue = $kiosk->harga_per_paket;
            $breakdown['kiosk_total'] += $revenue;
            $breakdown['kiosk_items'][] = [
                'nama' => $kiosk->nama,
                'hari_ke' => $kiosk->hari_ke,
                'harga_per_paket' => $kiosk->harga_per_paket,
                'revenue' => $revenue
            ];
        }

        return $breakdown;
    }

    private function getBoatBreakdown($startDate, $endDate)
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
            ->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')
            ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'boats.id',
                'boats.nama',
                'boats.harga_sewa',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
                DB::raw('SUM(boats.harga_sewa) as total_revenue')
            )
            ->groupBy('boats.id', 'boats.nama', 'boats.harga_sewa')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }
    

    private function getHomestayBreakdown($startDate, $endDate)
{
    return DB::table('orders')
        ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
        ->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')
        ->join('homestays', 'homestays.id_homestay', '=', 'paket_wisata_homestay.id_homestay')
        ->where('orders.status', 'paid')
        ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        ->select(
            'homestays.id',
            'homestays.nama',
            'homestays.harga_per_malam',
            DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
            DB::raw('SUM(homestays.harga_per_malam * paket_wisata_homestay.jumlah_malam) as total_revenue')
        )
        ->groupBy('homestays.id', 'homestays.nama', 'homestays.harga_per_malam')
        ->orderBy('total_revenue', 'desc')
        ->get();
}
    private function getCulinaryBreakdown($startDate, $endDate)
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
            ->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'culinaries.id_culinary',
                'culinaries.nama',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
                DB::raw('SUM(paket_culinaries.harga) as total_revenue')
            )
            ->groupBy('culinaries.id_culinary', 'culinaries.nama')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getKioskBreakdown($startDate, $endDate)
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
            ->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')
            ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'kiosks.id_kiosk',
                'kiosks.nama',
                'kiosks.harga_per_paket',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
                DB::raw('SUM(kiosks.harga_per_paket) as total_revenue')
            )
            ->groupBy('kiosks.id_kiosk', 'kiosks.nama', 'kiosks.harga_per_paket')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getDailySalesChart($startDate, $endDate)
    {
        $sales = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(base_amount) as total_revenue') // ✅ ALWAYS MYR
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'orders' => $sales->pluck('total_orders')->toArray(),
            'revenue' => $sales->pluck('total_revenue')->toArray(),
        ];
    }

    public function show($orderId)
    {
        // Get order
        $order = DB::table('orders')
            ->where('orders.id_order', $orderId)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Get order items with package details
        $orderItems = DB::table('order_items')
            ->join('paket_wisatas', 'order_items.id_paket', '=', 'paket_wisatas.id_paket')
            ->where('order_items.id_order', $orderId)
            ->select(
                'order_items.*',
                'paket_wisatas.nama_paket',
                'paket_wisatas.durasi_hari as paket_durasi',
                'paket_wisatas.harga_total as paket_harga_total'
            )
            ->get();

        // Calculate detailed breakdown for each item
        $itemsWithBreakdown = $orderItems->map(function($item) {
            $breakdown = $this->calculateRevenueBreakdown(
                $item->id_paket, 
                $item->durasi_hari ?? $item->paket_durasi
            );
            $item->breakdown = $breakdown;
            return $item;
        });

        // Calculate totals
        $totals = [
            'boat' => $itemsWithBreakdown->sum('breakdown.boat_total'),
            'homestay' => $itemsWithBreakdown->sum('breakdown.homestay_total'),
            'culinary' => $itemsWithBreakdown->sum('breakdown.culinary_total'),
            'kiosk' => $itemsWithBreakdown->sum('breakdown.kiosk_total'),
        ];

        // Get payment logs if exists
        $paymentLogs = DB::table('payment_logs')
            ->where('id_order', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.sales.detail', compact(
            'order',
            'itemsWithBreakdown',
            'totals',
            'paymentLogs'
        ));
    }

    private function calculateTotals($itemsWithBreakdown, $baseAmount)
{
    $vendorTotals = [
        'boat' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['boat_total'] ?? 0) * $i->jumlah_peserta; 
        }),
        'homestay' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['homestay_total'] ?? 0) * $i->jumlah_peserta; 
        }),
        'culinary' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['culinary_total'] ?? 0) * $i->jumlah_peserta; 
        }),
        'kiosk' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['kiosk_total'] ?? 0) * $i->jumlah_peserta; 
        }),
    ];

    // Hitung total semua biaya vendor
    $totalCosts = array_sum($vendorTotals);

    // MASUKKAN KEY 'company' KE DALAM ARRAY
    $vendorTotals['company'] = $baseAmount - $totalCosts;

    return $vendorTotals;
}

    public function downloadManifest($orderId)
{
    $order = DB::table('orders')->where('id_order', $orderId)->first();
    if (!$order) { abort(404); }

    $orderItems = DB::table('order_items')
        ->join('paket_wisatas', 'order_items.id_paket', '=', 'paket_wisatas.id_paket')
        ->where('order_items.id_order', $orderId)
        ->select('order_items.*', 'paket_wisatas.nama_paket', 'paket_wisatas.durasi_hari')
        ->get();

    $itemsWithBreakdown = $orderItems->map(function($item) {
        $item->breakdown = $this->calculateRevenueBreakdown($item->id_paket, $item->durasi_hari);
        return $item;
    });

    // MEMANGGIL FUNGSI YANG BARU DIBUAT
    $totals = $this->calculateTotals($itemsWithBreakdown, $order->base_amount);

    $pdf = \PDF::loadView('invoice.manifest', [
        'order' => $order,
        'items' => $itemsWithBreakdown,
        'totals' => $totals
    ]);

    return $pdf->download('Manifest-'.$orderId.'.pdf');
}
}