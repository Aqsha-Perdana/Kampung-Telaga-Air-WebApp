<?php

namespace App\Services\Sales;

use App\Models\Order;
use App\Services\OrderItemSnapshotService;
use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class SalesQueryService
{
    private const DASHBOARD_CACHE_PREFIX = 'admin.sales.dashboard.v7.';

    public function __construct(
        private readonly OrderItemSnapshotService $snapshotService,
        private readonly PaymentReconciliationService $paymentReconciliationService,
        private readonly SalesProfitCalculator $salesProfitCalculator,
        private readonly SalesSummaryService $salesSummaryService
    ) {
    }

    public function getDashboardData(array $filters): array
    {
        $filters = $this->normalizeDashboardFilters($filters);
        $cacheKey = self::DASHBOARD_CACHE_PREFIX . md5(json_encode([
            'start' => $filters['start_date'],
            'end' => $filters['end_date'],
            'status' => $filters['status'],
            'display_currency' => $filters['display_currency'],
            'payment_method' => $filters['payment_method'],
            'payment_channel' => $filters['payment_channel'],
            'gateway_fee_source' => $filters['gateway_fee_source'],
        ]));

        $cachedPayload = Cache::get($cacheKey);
        if (is_array($cachedPayload) && !$this->shouldBypassDashboardCache($cachedPayload['recentTransactions'] ?? collect())) {
            return $cachedPayload;
        }

        $orderItems = $this->queryOrderItems($filters);
        $recentTransactions = $this->salesProfitCalculator->buildGroupedOrders($orderItems);
        $recentTransactions = $this->reconcileEstimatedGatewayFees($recentTransactions);

        $payload = [
            'recentTransactions' => $recentTransactions,
            'summary' => $this->salesSummaryService->buildSummary(
                $recentTransactions,
                $filters
            ),
            'entityBreakdown' => $this->getEntityBreakdown($filters),
            'chartData' => $this->getDailySalesChart($filters),
            'availableDisplayCurrencies' => $this->getAvailableDisplayCurrencies($filters),
            'availablePaymentMethods' => $this->getAvailablePaymentMethods($filters),
            'availablePaymentChannels' => $this->getAvailablePaymentChannels($filters),
            'availableFeeSources' => $this->getAvailableFeeSources($filters),
        ];

        Cache::put($cacheKey, $payload, now()->addSeconds(30));

        return $payload;
    }

    public function getOrderDetailData(string $orderId): ?array
    {
        $order = DB::table('orders')
            ->where('orders.id_order', $orderId)
            ->first();

        if (!$order) {
            return null;
        }

        if (
            ($order->payment_method === 'stripe' && strtolower((string) ($order->gateway_fee_source ?? '')) !== 'actual')
            || $order->payment_method === 'xendit'
        ) {
            $eloquentOrder = Order::where('id_order', $orderId)->first();

            if ($eloquentOrder) {
                $syncResult = $this->paymentReconciliationService->refreshGatewayFeeIfAvailable($eloquentOrder);
                if (($syncResult['updated'] ?? false) === true) {
                    $order->payment_channel = $syncResult['payment_channel'] ?? $order->payment_channel;
                    $order->gateway_fee_amount = $syncResult['fee_amount'] ?? $order->gateway_fee_amount;
                    $order->gateway_net_amount = $syncResult['net_amount'] ?? $order->gateway_net_amount;
                    $order->gateway_fee_source = $syncResult['fee_source'] ?? $order->gateway_fee_source;
                }
            }
        }

        $normalizedGatewayAmounts = resolve_gateway_amounts(
            $order->base_amount ?? $order->total_amount ?? 0,
            $order->gateway_fee_amount ?? 0,
            $order->gateway_net_amount ?? null
        );
        $order->gateway_fee_amount = $normalizedGatewayAmounts['fee_amount'];
        $order->gateway_net_amount = $normalizedGatewayAmounts['net_amount'];

        $orderItems = DB::table('order_items')
            ->where('order_items.id_order', $orderId)
            ->select('order_items.*')
            ->get();

        $itemsWithBreakdown = $orderItems->map(function ($item) {
            $item->breakdown = $this->snapshotService->breakdownFromOrderItem($item);

            return $item;
        });

        $totals = [
            'boat' => $itemsWithBreakdown->sum('breakdown.boat_total'),
            'homestay' => $itemsWithBreakdown->sum('breakdown.homestay_total'),
            'culinary' => $itemsWithBreakdown->sum('breakdown.culinary_total'),
            'kiosk' => $itemsWithBreakdown->sum('breakdown.kiosk_total'),
        ];

        $paymentLogs = DB::table('payment_logs')
            ->where('id_order', $orderId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $gatewayAmounts = resolve_gateway_amounts(
                    $log->amount ?? 0,
                    $log->fee_amount ?? 0,
                    $log->net_amount ?? null
                );

                $log->fee_amount = $gatewayAmounts['fee_amount'];
                $log->net_amount = $gatewayAmounts['net_amount'];

                return $log;
            });

        return [
            'order' => $order,
            'itemsWithBreakdown' => $itemsWithBreakdown,
            'totals' => $totals,
            'paymentLogs' => $paymentLogs,
            'financialSummary' => $this->salesProfitCalculator->buildFinancialSummary($order, $orderItems),
        ];
    }

    private function normalizeDashboardFilters(array $filters): array
    {
        return [
            'start_date' => (string) ($filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d')),
            'end_date' => (string) ($filters['end_date'] ?? now()->format('Y-m-d')),
            'status' => (string) ($filters['status'] ?? 'all'),
            'display_currency' => (string) ($filters['display_currency'] ?? 'all'),
            'payment_method' => (string) ($filters['payment_method'] ?? 'all'),
            'payment_channel' => (string) ($filters['payment_channel'] ?? 'all'),
            'gateway_fee_source' => (string) ($filters['gateway_fee_source'] ?? 'all'),
        ];
    }

    private function queryOrderItems(array $filters): Collection
    {
        $query = DB::table('orders')
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order');

        $this->applyDashboardFilters($query, $filters);

        return $query->select(
            'orders.id_order',
            'orders.customer_name',
            'orders.base_amount',
            'orders.payment_amount',
            'orders.payment_currency',
            'orders.display_currency',
            'orders.display_amount',
            'orders.display_exchange_rate',
            'orders.payment_method',
            'orders.payment_channel',
            'orders.gateway_fee_amount',
            'orders.gateway_net_amount',
            'orders.gateway_fee_source',
            'orders.refund_fee',
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
    }

    private function getEntityBreakdown(array $filters): array
    {
        $orderItems = DB::table('order_items')
            ->join('orders', 'orders.id_order', '=', 'order_items.id_order')
            ->select('order_items.*', 'orders.customer_name', 'orders.created_at');

        $this->applyDashboardFilters($orderItems, $filters, ['status']);

        if ($filters['status'] === 'all') {
            $orderItems->whereIn('orders.status', $this->salesSummaryService->operatingStatuses());
        } else {
            $orderItems->where('orders.status', $filters['status']);
        }

        $orderItems = $orderItems->get();

        return [
            'boats' => $this->snapshotService->aggregateResourceEntries($orderItems, 'boats')
                ->map(fn (array $entry) => (object) [
                    'id' => $entry['id'],
                    'nama' => $entry['name'],
                    'harga_sewa' => $entry['price_per_unit'],
                    'total_orders' => $entry['total_orders'],
                    'total_revenue' => $entry['total_revenue'],
                ]),
            'homestays' => $this->snapshotService->aggregateResourceEntries($orderItems, 'homestays')
                ->map(fn (array $entry) => (object) [
                    'id' => $entry['id'],
                    'nama' => $entry['name'],
                    'harga_per_malam' => $entry['price_per_unit'],
                    'total_orders' => $entry['total_orders'],
                    'total_revenue' => $entry['total_revenue'],
                ]),
            'culinaries' => $this->snapshotService->aggregateResourceEntries($orderItems, 'culinary')
                ->map(fn (array $entry) => (object) [
                    'id_culinary' => $entry['id'],
                    'nama' => $entry['name'],
                    'total_orders' => $entry['total_orders'],
                    'total_revenue' => $entry['total_revenue'],
                ]),
            'kiosks' => $this->snapshotService->aggregateResourceEntries($orderItems, 'kiosks')
                ->map(fn (array $entry) => (object) [
                    'id_kiosk' => $entry['id'],
                    'nama' => $entry['name'],
                    'harga_per_paket' => $entry['price_per_unit'],
                    'total_orders' => $entry['total_orders'],
                    'total_revenue' => $entry['total_revenue'],
                ]),
        ];
    }

    private function getDailySalesChart(array $filters): array
    {
        $sales = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(base_amount) as total_revenue')
            );

        $this->applyDashboardFilters($sales, $filters, ['status'], 'created_at');

        if ($filters['status'] === 'all') {
            $sales->whereIn('status', $this->salesSummaryService->operatingStatuses());
        } else {
            $sales->where('status', $filters['status']);
        }

        $sales = $sales
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('d M'))->toArray(),
            'orders' => $sales->pluck('total_orders')->toArray(),
            'revenue' => $sales->pluck('total_revenue')->toArray(),
        ];
    }

    private function getAvailableDisplayCurrencies(array $filters): Collection
    {
        $query = DB::table('orders');
        $this->applyDashboardFilters($query, $filters, ['display_currency'], 'created_at');

        return $query
            ->select('display_currency')
            ->distinct()
            ->get()
            ->pluck('display_currency')
            ->filter()
            ->push('MYR')
            ->unique()
            ->sort()
            ->values();
    }

    private function getAvailablePaymentMethods(array $filters): Collection
    {
        $query = DB::table('orders');
        $this->applyDashboardFilters($query, $filters, ['payment_method'], 'created_at');

        return $query
            ->select('payment_method')
            ->distinct()
            ->get()
            ->pluck('payment_method')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function getAvailablePaymentChannels(array $filters): Collection
    {
        $query = DB::table('orders');
        $this->applyDashboardFilters($query, $filters, ['payment_channel'], 'created_at');

        return $query
            ->select('payment_channel')
            ->distinct()
            ->get()
            ->pluck('payment_channel')
            ->filter()
            ->unique()
            ->sortBy(fn ($channel) => payment_channel_label($channel))
            ->values();
    }

    private function getAvailableFeeSources(array $filters): Collection
    {
        $query = DB::table('orders');
        $this->applyDashboardFilters($query, $filters, ['gateway_fee_source'], 'created_at');

        return $query
            ->select('gateway_fee_source')
            ->distinct()
            ->get()
            ->pluck('gateway_fee_source')
            ->map(function ($source) {
                $normalized = strtolower(trim((string) $source));

                return $normalized === '' ? 'unknown' : $normalized;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function applyDashboardFilters(
        Builder $query,
        array $filters,
        array $exclude = [],
        string $dateColumn = 'orders.created_at'
    ): void {
        $query->whereBetween($dateColumn, [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);

        if (!in_array('status', $exclude, true) && $filters['status'] !== 'all') {
            $query->where('orders.status', $filters['status']);
        }

        if (!in_array('display_currency', $exclude, true) && $filters['display_currency'] !== 'all') {
            if ($filters['display_currency'] === 'MYR') {
                $query->where(function ($builder) {
                    $builder->whereNull('orders.display_currency')
                        ->orWhere('orders.display_currency', 'MYR');
                });
            } else {
                $query->where('orders.display_currency', $filters['display_currency']);
            }
        }

        if (!in_array('payment_method', $exclude, true) && $filters['payment_method'] !== 'all') {
            $query->where('orders.payment_method', $filters['payment_method']);
        }

        if (!in_array('payment_channel', $exclude, true) && $filters['payment_channel'] !== 'all') {
            $query->where('orders.payment_channel', $filters['payment_channel']);
        }

        if (!in_array('gateway_fee_source', $exclude, true) && $filters['gateway_fee_source'] !== 'all') {
            $this->applyGatewayFeeSourceFilter($query, $filters['gateway_fee_source']);
        }
    }

    private function applyGatewayFeeSourceFilter(Builder $query, string $gatewayFeeSource): void
    {
        $normalized = strtolower(trim($gatewayFeeSource));

        if ($normalized === 'unknown') {
            $query->where(function ($builder) {
                $builder->whereNull('orders.gateway_fee_source')
                    ->orWhere('orders.gateway_fee_source', '');
            });

            return;
        }

        $query->whereRaw('LOWER(COALESCE(orders.gateway_fee_source, "")) = ?', [$normalized]);
    }

    private function reconcileEstimatedGatewayFees(Collection $recentTransactions): Collection
    {
        return $recentTransactions->map(function ($order) {
            if (
                !in_array(($order->payment_method ?? null), ['xendit', 'stripe'], true)
                || (
                    ($order->payment_method ?? null) === 'stripe'
                    && strtolower((string) ($order->gateway_fee_source ?? '')) === 'actual'
                )
            ) {
                return $order;
            }

            $eloquentOrder = Order::where('id_order', $order->id_order)->first();
            if (!$eloquentOrder) {
                return $order;
            }

            $syncResult = $this->paymentReconciliationService->refreshGatewayFeeIfAvailable($eloquentOrder);
            if (($syncResult['updated'] ?? false) !== true) {
                return $order;
            }

            $order->payment_channel = $syncResult['payment_channel'] ?? $order->payment_channel;
            $order->gateway_fee_amount = (float) ($syncResult['fee_amount'] ?? $order->gateway_fee_amount ?? 0);
            $order->gateway_net_amount = (float) ($syncResult['net_amount'] ?? $order->gateway_net_amount ?? 0);
            $order->gateway_fee_source = $syncResult['fee_source'] ?? $order->gateway_fee_source;
            $order->company_profit = $this->salesProfitCalculator->calculateReportedProfit(
                (string) ($order->status ?? ''),
                (float) ($order->original_profit ?? 0),
                (float) ($order->gateway_fee_amount ?? 0),
                (float) ($order->refund_fee ?? 0)
            );

            return $order;
        })->values();
    }

    private function shouldBypassDashboardCache(Collection|array $recentTransactions): bool
    {
        $orders = $recentTransactions instanceof Collection ? $recentTransactions : collect($recentTransactions);

        return $orders->contains(function ($order) {
            return in_array(($order->payment_method ?? null), ['xendit', 'stripe'], true)
                && strtolower((string) ($order->gateway_fee_source ?? '')) !== 'actual';
        });
    }
}
