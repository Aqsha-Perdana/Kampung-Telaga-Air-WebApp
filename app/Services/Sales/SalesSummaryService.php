<?php

namespace App\Services\Sales;

use App\Services\Finance\OrderFinancialProjectionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesSummaryService
{
    public function __construct(private readonly OrderFinancialProjectionService $projectionService)
    {
    }

    public function buildSummary(Collection $recentTransactions, array $filters): array
    {
        $operatingOrders = $recentTransactions->whereIn('status', $this->operatingStatuses());
        $recognizedOrders = $recentTransactions->whereIn('status', $this->recognizedStatuses());

        return [
            'total_revenue' => $operatingOrders->sum('base_amount'),
            'total_orders' => $recentTransactions->count(),
            'recognized_orders' => $recognizedOrders->count(),
            'boat_cost_total' => $operatingOrders->sum('revenue_breakdown.boat_total'),
            'homestay_cost_total' => $operatingOrders->sum('revenue_breakdown.homestay_total'),
            'culinary_cost_total' => $operatingOrders->sum('revenue_breakdown.culinary_total'),
            'kiosk_cost_total' => $operatingOrders->sum('revenue_breakdown.kiosk_total'),
            'net_profit_after_gateway_fee' => $recognizedOrders->sum('company_profit'),
            'gateway_fee_total' => (float) $recognizedOrders->sum('gateway_fee_amount'),
            'gateway_fee_groups' => $this->buildGatewayFeeBreakdown($recognizedOrders),
            'currency_breakdown' => $this->getDisplayCurrencyBreakdown($filters),
        ];
    }

    public function operatingStatuses(): array
    {
        return $this->projectionService->operatingStatuses();
    }

    public function recognizedStatuses(): array
    {
        return $this->projectionService->recognizedStatuses();
    }

    private function buildGatewayFeeBreakdown(Collection $recognizedOrders): Collection
    {
        return $recognizedOrders
            ->filter(fn ($order) => (float) ($order->gateway_fee_amount ?? 0) > 0)
            ->groupBy(function ($order) {
                return (string) ($order->payment_method ?? 'unknown') . '|' . (string) ($order->payment_channel ?? '');
            })
            ->map(function ($orders, $groupKey) {
                [$method, $channel] = array_pad(explode('|', (string) $groupKey, 2), 2, '');

                return (object) [
                    'payment_method' => $method,
                    'payment_channel' => $channel,
                    'label' => payment_descriptor($method, $channel),
                    'transactions' => $orders->count(),
                    'gross_amount' => (float) $orders->sum('base_amount'),
                    'gateway_fee_total' => (float) $orders->sum('gateway_fee_amount'),
                    'net_settlement_total' => (float) $orders->sum('gateway_net_amount'),
                    'profit_after_fee_total' => (float) $orders->sum('company_profit'),
                    'source_breakdown' => $orders
                        ->groupBy(fn ($order) => gateway_fee_source_label($order->gateway_fee_source ?? null))
                        ->map(fn ($sourceOrders) => (int) $sourceOrders->count())
                        ->all(),
                    'details' => $orders
                        ->map(fn ($order) => (object) [
                            'id_order' => $order->id_order,
                            'customer_name' => $order->customer_name,
                            'gateway_fee_amount' => (float) ($order->gateway_fee_amount ?? 0),
                            'gateway_fee_source' => $order->gateway_fee_source,
                            'base_amount' => (float) ($order->base_amount ?? 0),
                            'gateway_net_amount' => (float) ($order->gateway_net_amount ?? 0),
                            'company_profit' => (float) ($order->company_profit ?? 0),
                            'created_at' => $order->created_at,
                        ])
                        ->sortByDesc('created_at')
                        ->values(),
                ];
            })
            ->sortByDesc('gateway_fee_total')
            ->values();
    }

    private function getDisplayCurrencyBreakdown(array $filters): Collection
    {
        $ordersQuery = DB::table('orders')
            ->select('display_currency', 'base_amount', 'display_amount', 'display_exchange_rate');

        $ordersQuery->whereBetween('created_at', [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);

        if (($filters['status'] ?? 'all') === 'all') {
            $ordersQuery->whereIn('status', $this->operatingStatuses());
        } else {
            $ordersQuery->where('status', $filters['status']);
        }

        if (($filters['payment_method'] ?? 'all') !== 'all') {
            $ordersQuery->where('payment_method', $filters['payment_method']);
        }

        if (($filters['payment_channel'] ?? 'all') !== 'all') {
            $ordersQuery->where('payment_channel', $filters['payment_channel']);
        }

        if (($filters['gateway_fee_source'] ?? 'all') !== 'all') {
            $source = strtolower(trim((string) $filters['gateway_fee_source']));
            if ($source === 'unknown') {
                $ordersQuery->where(function ($builder) {
                    $builder->whereNull('gateway_fee_source')
                        ->orWhere('gateway_fee_source', '');
                });
            } else {
                $ordersQuery->whereRaw('LOWER(COALESCE(gateway_fee_source, "")) = ?', [$source]);
            }
        }

        $orders = $ordersQuery->get();

        return $orders
            ->groupBy(fn ($order) => $order->display_currency ?: 'MYR')
            ->map(function ($currencyOrders, $currency) {
                return (object) [
                    'currency' => $currency,
                    'total_orders' => $currencyOrders->count(),
                    'total_revenue_myr' => $currencyOrders->sum('base_amount'),
                    'total_display_amount' => $currencyOrders->sum('display_amount'),
                    'avg_exchange_rate' => $currencyOrders->where('display_exchange_rate', '>', 0)->avg('display_exchange_rate') ?? 1,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a->currency === 'MYR') {
                    return -1;
                }

                if ($b->currency === 'MYR') {
                    return 1;
                }

                return $b->total_revenue_myr <=> $a->total_revenue_myr;
            })
            ->values();
    }
}
