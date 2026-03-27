<?php

namespace App\Http\Controllers;

use App\Services\OrderItemSnapshotService;
use App\Services\CustomerEmailService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends Controller
{
    public function __construct(
        private readonly OrderItemSnapshotService $snapshotService,
        private readonly CustomerEmailService $customerEmailService
    )
    {
    }

    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status', 'all');
        $displayCurrency = $request->input('display_currency', 'all'); // UPDATED: display_currency filter

        $cacheKey = 'admin.sales.index.v3.' . md5(json_encode([
            'start' => $startDate,
            'end' => $endDate,
            'status' => $status,
            'display_currency' => $displayCurrency,
        ]));

        $cachedPayload = Cache::get($cacheKey);
        if (is_array($cachedPayload)) {
            return view('admin.sales.index', array_merge($cachedPayload, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => $status,
                'displayCurrency' => $displayCurrency,
            ]));
        }

        // Build base query for orders with items
        $query = DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
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

        // Get all order items with snapshot details
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
            'order_items.subtotal',
            'order_items.boat_cost_total',
            'order_items.homestay_cost_total',
            'order_items.culinary_cost_total',
            'order_items.kiosk_cost_total',
            'order_items.vendor_cost_total',
            'order_items.company_profit_total',
            'order_items.boat_cost_items',
            'order_items.homestay_cost_items',
            'order_items.culinary_cost_items',
            'order_items.kiosk_cost_items'
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
                $breakdown = $this->snapshotService->breakdownFromOrderItem($item);

                // Harga paket dan biaya vendor dihitung flat per paket.
                $totalBreakdown['boat_total'] += ($breakdown['boat_total'] ?? 0);
                $totalBreakdown['homestay_total'] += ($breakdown['homestay_total'] ?? 0);
                $totalBreakdown['culinary_total'] += ($breakdown['culinary_total'] ?? 0);
                $totalBreakdown['kiosk_total'] += ($breakdown['kiosk_total'] ?? 0);
            }

            // Hitung profit: Base Amount (Apa yang dibayar tamu) - Total Biaya Vendor
            $totalVendorCosts = (float) $items->sum(function ($item) {
                return (float) ($item->vendor_cost_total ?? 0);
            });

            if ($totalVendorCosts <= 0.0) {
                $totalVendorCosts = array_sum($totalBreakdown);
            }

            $companyProfit = (float) $items->sum(function ($item) {
                return (float) ($item->company_profit_total ?? ((float) ($item->subtotal ?? 0) - (float) ($item->vendor_cost_total ?? 0)));
            });

            if ($companyProfit == 0.0 && (float) $firstItem->base_amount > 0) {
                $companyProfit = (float) $firstItem->base_amount - $totalVendorCosts;
            }

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
        
        $companyRevenue = $paidOrders->sum('company_profit');

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
        $paidOrderItemsForBreakdown = DB::table('order_items')
            ->join('orders', 'orders.id_order', '=', 'order_items.id_order')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('order_items.*', 'orders.customer_name', 'orders.created_at')
            ->get();

        $entityBreakdown = [
            'boats' => $this->getBoatBreakdown($paidOrderItemsForBreakdown),
            'homestays' => $this->getHomestayBreakdown($paidOrderItemsForBreakdown),
            'culinaries' => $this->getCulinaryBreakdown($paidOrderItemsForBreakdown),
            'kiosks' => $this->getKioskBreakdown($paidOrderItemsForBreakdown),
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

        $viewPayload = compact(
            'groupedOrders',
            'summary',
            'entityBreakdown',
            'chartData',
            'availableDisplayCurrencies',
        );

        Cache::put($cacheKey, $viewPayload, now()->addSeconds(30));

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

    private function getBoatBreakdown($orderItems)
    {
        return $this->snapshotService->aggregateResourceEntries($orderItems, 'boats')
            ->map(fn (array $entry) => (object) [
                'id' => $entry['id'],
                'nama' => $entry['name'],
                'harga_sewa' => $entry['price_per_unit'],
                'total_orders' => $entry['total_orders'],
                'total_revenue' => $entry['total_revenue'],
            ]);
    }

    private function getHomestayBreakdown($orderItems)
    {
        return $this->snapshotService->aggregateResourceEntries($orderItems, 'homestays')
            ->map(fn (array $entry) => (object) [
                'id' => $entry['id'],
                'nama' => $entry['name'],
                'harga_per_malam' => $entry['price_per_unit'],
                'total_orders' => $entry['total_orders'],
                'total_revenue' => $entry['total_revenue'],
            ]);
    }

    private function getCulinaryBreakdown($orderItems)
    {
        return $this->snapshotService->aggregateResourceEntries($orderItems, 'culinary')
            ->map(fn (array $entry) => (object) [
                'id_culinary' => $entry['id'],
                'nama' => $entry['name'],
                'total_orders' => $entry['total_orders'],
                'total_revenue' => $entry['total_revenue'],
            ]);
    }

    private function getKioskBreakdown($orderItems)
    {
        return $this->snapshotService->aggregateResourceEntries($orderItems, 'kiosks')
            ->map(fn (array $entry) => (object) [
                'id_kiosk' => $entry['id'],
                'nama' => $entry['name'],
                'harga_per_paket' => $entry['price_per_unit'],
                'total_orders' => $entry['total_orders'],
                'total_revenue' => $entry['total_revenue'],
            ]);
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

        // Get order items with snapshot details
        $orderItems = DB::table('order_items')
            ->where('order_items.id_order', $orderId)
            ->select('order_items.*')
            ->get();

        // Calculate detailed breakdown for each item
        $itemsWithBreakdown = $orderItems->map(function($item) {
            $item->breakdown = $this->snapshotService->breakdownFromOrderItem($item);
            return $item;
        });

        // Calculate totals
        $totals = [
            'boat' => $itemsWithBreakdown->sum('breakdown.boat_total'),
            'homestay' => $itemsWithBreakdown->sum('breakdown.homestay_total'),
            'culinary' => $itemsWithBreakdown->sum('breakdown.culinary_total'),
            'kiosk' => $itemsWithBreakdown->sum('breakdown.kiosk_total'),
        ];

        $vendorTotalSnapshot = array_sum($totals);
        $originalProfitSnapshot = (float) $orderItems->sum(function ($item) {
            return (float) ($item->company_profit_total ?? ((float) ($item->subtotal ?? 0) - (float) ($item->vendor_cost_total ?? 0)));
        });

        if ($originalProfitSnapshot == 0.0) {
            $originalProfitSnapshot = (float) ($order->base_amount ?? $order->total_amount ?? 0) - $vendorTotalSnapshot;
        }

        $reportedProfitImpact = $order->status === 'refunded'
            ? (float) ($order->refund_fee ?? 0)
            : $originalProfitSnapshot;

        $financialSummary = [
            'vendor_total' => $vendorTotalSnapshot,
            'original_profit' => $originalProfitSnapshot,
            'reported_profit_impact' => $reportedProfitImpact,
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
            'paymentLogs',
            'financialSummary'
        ));
    }

    private function calculateTotals($itemsWithBreakdown, $baseAmount)
{
    $vendorTotals = [
        'boat' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['boat_total'] ?? 0); 
        }),
        'homestay' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['homestay_total'] ?? 0); 
        }),
        'culinary' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['culinary_total'] ?? 0); 
        }),
        'kiosk' => $itemsWithBreakdown->sum(function($i) { 
            return ($i->breakdown['kiosk_total'] ?? 0); 
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
            ->where('order_items.id_order', $orderId)
            ->select('order_items.*')
            ->get();

        $itemsWithBreakdown = $orderItems->map(function($item) {
            $item->breakdown = $this->snapshotService->breakdownFromOrderItem($item);
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

    public function approveRefund(Request $request, $id_order, RefundService $refundService)
    {
        $result = $refundService->approveRefund((string) $id_order);

        if (($result['ok'] ?? false) !== true) {
            return redirect()->back()->with('error', $result['message']);
        }

        if (($result['code'] ?? null) === 'already_refunded') {
            return redirect()->back()->with('success', 'This refund has already been processed.');
        }

        return redirect()->back()->with(
            'success',
            'Refund approved. RM '
            . number_format((float) ($result['refund_amount'] ?? 0), 2)
            . ' has been returned (Refund fee: RM '
            . number_format((float) ($result['refund_fee'] ?? 0), 2)
            . ').'
        );
    }

    public function rejectRefund(Request $request, $id_order)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $order = \App\Models\Order::findOrFail($id_order);

        if ($order->status !== 'refund_requested') {
            return redirect()->back()->with('error', 'This order is not in refund-requested status.');
        }

        if ($order->refund_status === 'processing') {
            return redirect()->back()->with('error', 'Refund is currently processing and cannot be rejected right now.');
        }

        $order->update([
            'status' => 'paid',
            'refund_status' => 'rejected',
            'refund_rejected_reason' => $request->reason,
            'refund_failure_reason' => null,
        ]);

        $order->loadMissing('items');
        $this->customerEmailService->sendRefundRejected($order);

        return redirect()->back()->with('success', 'Refund request rejected. Order status has been restored to Paid.');
    }
}
