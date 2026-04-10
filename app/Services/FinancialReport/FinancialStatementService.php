<?php

namespace App\Services\FinancialReport;

use App\Services\Finance\OrderFinancialProjectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialStatementService
{
    public function __construct(
        private readonly OrderCostCalculator $costCalculator,
        private readonly OrderFinancialProjectionService $projectionService
    )
    {
    }

    public function getProfitLoss($startDate, $endDate): array
    {
        [$startAt, $endAt] = $this->normalizeDateTimeRange($startDate, $endDate);
        [$startDateOnly, $endDateOnly] = $this->normalizeDateRange($startDate, $endDate);

        $orders = $this->projectionService->normalizeOrders(
            DB::table('orders')
            ->whereIn('status', $this->projectionService->recognizedStatuses())
            ->whereBetween('created_at', [$startAt, $endAt])
            ->get()
        );

        $orderItemsByOrderId = DB::table('order_items')
            ->whereIn('id_order', $orders->pluck('id_order')->all())
            ->get()
            ->groupBy('id_order');

        $refundFeeIncome = $orders
            ->where('status', 'refunded')
            ->sum(fn ($order) => (float) ($order->refund_fee ?? 0));

        $grossTourPackageSales = 0.0;
        $salesDiscounts = 0.0;
        $revenue = 0.0;
        $paymentGatewayFees = $orders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));

        $costOfSales = 0.0;
        $revenueBreakdown = [];

        $projections = $orders->map(function ($order) use ($orderItemsByOrderId) {
            $orderItems = collect($orderItemsByOrderId->get($order->id_order, []));

            return $this->projectionService->projectOrder($order, $orderItems);
        });

        foreach ($projections as $projection) {
            if (($projection['status'] ?? null) !== 'refunded') {
                $grossTourPackageSales += (float) $projection['gross_revenue'];
                $salesDiscounts += (float) $projection['sales_discount'];
                $revenue += (float) $projection['revenue'];
            }

            $costOfSales += (float) $projection['cost_of_sales'];
            $revenueBreakdown[] = $projection;
        }

        $grossProfit = $revenue - $costOfSales;

        $operatingExpenses = DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$startDateOnly, $endDateOnly])
            ->get();

        $expensesByNature = $operatingExpenses->groupBy('kategori')->map(function ($items) {
            return [
                'count' => $items->count(),
                'amount' => $items->sum('jumlah'),
            ];
        })->toArray();

        $expensesByNature['Gateway Fee (MDR)'] = [
            'count' => $orders->filter(fn ($order) => (float) ($order->gateway_fee_amount ?? 0) > 0)->count(),
            'amount' => $paymentGatewayFees,
        ];

        $paymentGatewayFeeReport = $this->buildGatewayFeeReport($orders, 'created_at');

        $totalOperatingExpenses = (float) $operatingExpenses->sum('jumlah') + $paymentGatewayFees;
        $operatingProfit = $grossProfit - $totalOperatingExpenses;

        $otherIncome = $refundFeeIncome;
        $otherExpenses = 0.0;
        $profitBeforeTax = $operatingProfit + $otherIncome - $otherExpenses;

        $taxRate = 0.0;
        $taxExpense = $profitBeforeTax * $taxRate;
        $profitForPeriod = $profitBeforeTax - $taxExpense;

        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $operatingProfitMargin = $revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0;
        $netProfitMargin = $revenue > 0 ? ($profitForPeriod / $revenue) * 100 : 0;

        return [
            'period' => [
                'start' => $startAt,
                'end' => $endAt,
                'currency' => 'MYR',
            ],
            'revenue' => [
                'gross_tour_package_sales' => $grossTourPackageSales,
                'sales_discounts' => $salesDiscounts,
                'net_tour_package_sales' => $revenue,
                'tour_package_sales' => $revenue,
                'other_revenue' => 0,
                'total_revenue' => $revenue,
            ],
            'cost_of_sales' => [
                'boat_services' => $projections->sum(fn ($projection) => (float) data_get($projection, 'vendor_breakdown.boat_total', 0)),
                'homestay_services' => $projections->sum(fn ($projection) => (float) data_get($projection, 'vendor_breakdown.homestay_total', 0)),
                'culinary_services' => $projections->sum(fn ($projection) => (float) data_get($projection, 'vendor_breakdown.culinary_total', 0)),
                'kiosk_services' => $projections->sum(fn ($projection) => (float) data_get($projection, 'vendor_breakdown.kiosk_total', 0)),
                'total_cost_of_sales' => $costOfSales,
            ],
            'gross_profit' => [
                'amount' => $grossProfit,
                'margin_percentage' => $grossProfitMargin,
            ],
            'operating_expenses' => [
                'by_nature' => $expensesByNature,
                'manual_operating_expenses' => (float) $operatingExpenses->sum('jumlah'),
                'payment_gateway_fees' => $paymentGatewayFees,
                'payment_gateway_fee_report' => $paymentGatewayFeeReport,
                'total_operating_expenses' => $totalOperatingExpenses,
            ],
            'operating_profit' => [
                'amount' => $operatingProfit,
                'margin_percentage' => $operatingProfitMargin,
            ],
            'other_items' => [
                'refund_fee_income' => $refundFeeIncome,
                'other_income' => $otherIncome,
                'other_expenses' => $otherExpenses,
                'net_other_items' => $otherIncome - $otherExpenses,
            ],
            'profit_before_tax' => [
                'amount' => $profitBeforeTax,
            ],
            'tax_expense' => [
                'current_tax' => $taxExpense,
                'deferred_tax' => 0,
                'total_tax' => $taxExpense,
                'effective_tax_rate' => $taxRate * 100,
            ],
            'profit_for_period' => [
                'amount' => $profitForPeriod,
                'margin_percentage' => $netProfitMargin,
            ],
            'transactions' => [
                'total_orders' => $orders->count(),
                'total_customers' => $orders->unique('customer_email')->count(),
                'total_gateway_fees' => $paymentGatewayFees,
            ],
            'revenue_breakdown' => $revenueBreakdown,
        ];
    }

    public function getCashFlow($startDate, $endDate): array
    {
        [$startAt, $endAt] = $this->normalizeDateTimeRange($startDate, $endDate);
        [$startDateOnly, $endDateOnly] = $this->normalizeDateRange($startDate, $endDate);
        $openingBalanceContext = $this->resolveOpeningCashBalance($startDateOnly);

        $receiptOrders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->get()
            ->map(function ($order) {
                $gatewayAmounts = resolve_gateway_amounts(
                    $order->base_amount ?? 0,
                    $order->gateway_fee_amount ?? 0,
                    $order->gateway_net_amount ?? null
                );

                $order->gateway_fee_amount = $gatewayAmounts['fee_amount'];
                $order->gateway_net_amount = $gatewayAmounts['net_amount'];

                return $order;
            });

        $cashFromCustomersGross = (float) $receiptOrders->sum(fn ($order) => (float) ($order->base_amount ?? 0));

        $gatewayFeesPaid = (float) $receiptOrders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));

        $netSettlementFromCustomers = (float) $receiptOrders->sum(fn ($order) => (float) ($order->gateway_net_amount ?? 0));

        $paymentMethodBreakdown = $receiptOrders
            ->groupBy('payment_method')
            ->mapWithKeys(function ($item) {
                return [
                    ($item->first()->payment_method ?? 'unknown') => [
                        'count' => $item->count(),
                        'gross_amount' => (float) $item->sum(fn ($order) => (float) ($order->base_amount ?? 0)),
                        'fee_amount' => (float) $item->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0)),
                        'net_amount' => (float) $item->sum(fn ($order) => (float) ($order->gateway_net_amount ?? 0)),
                    ],
                ];
            })
            ->toArray();

        $receiptTransactionCount = $receiptOrders->count();

        $refundsPaidToCustomers = (float) DB::table('orders')
            ->where('status', 'refunded')
            ->whereNotNull('refunded_at')
            ->whereBetween('refunded_at', [$startAt, $endAt])
            ->sum('refund_amount');

        $refundTransactions = DB::table('orders')
            ->where('status', 'refunded')
            ->whereNotNull('refunded_at')
            ->whereBetween('refunded_at', [$startAt, $endAt])
            ->count();

        $orders = $receiptOrders->whereIn('status', ['paid', 'confirmed', 'completed'])->values();

        $cashToSuppliers = 0.0;
        $supplierBreakdown = [
            'boat_owners' => 0.0,
            'homestay_owners' => 0.0,
            'culinary_providers' => 0.0,
            'kiosk_owners' => 0.0,
        ];

        foreach ($orders as $order) {
            $orderCost = $this->costCalculator->calculateOrderCost((string) $order->id_order);
            $cashToSuppliers += (float) $orderCost['total'];
            $supplierBreakdown['boat_owners'] += (float) $orderCost['breakdown']['boats']['total'];
            $supplierBreakdown['homestay_owners'] += (float) $orderCost['breakdown']['homestays']['total'];
            $supplierBreakdown['culinary_providers'] += (float) $orderCost['breakdown']['culinary']['total'];
            $supplierBreakdown['kiosk_owners'] += (float) $orderCost['breakdown']['kiosks']['total'];
        }

        $cashForOperatingExpenses = (float) DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$startDateOnly, $endDateOnly])
            ->sum('jumlah');

        $operatingExpensesByCategory = DB::table('beban_operasionals')
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->whereBetween('tanggal', [$startDateOnly, $endDateOnly])
            ->groupBy('kategori')
            ->get()
            ->pluck('total', 'kategori')
            ->toArray();

        $totalCashPayments = $cashToSuppliers
            + $gatewayFeesPaid
            + $refundsPaidToCustomers
            + $cashForOperatingExpenses;

        $netCashFromOperating = $cashFromCustomersGross - $totalCashPayments;

        $cashForInvestments = 0.0;
        $cashFromInvestments = 0.0;
        $netCashFromInvesting = $cashFromInvestments - $cashForInvestments;

        $cashFromFinancing = 0.0;
        $cashForFinancing = 0.0;
        $netCashFromFinancing = $cashFromFinancing - $cashForFinancing;

        $netCashMovement = $netCashFromOperating + $netCashFromInvesting + $netCashFromFinancing;

        $openingCash = (float) $openingBalanceContext['amount'];
        $closingCash = $openingCash + $netCashMovement;

        return [
            'period' => [
                'start' => $startAt,
                'end' => $endAt,
                'currency' => 'MYR',
            ],
            'operating_activities' => [
                'cash_receipts' => [
                    'from_customers_gross' => $cashFromCustomersGross,
                    'from_customers' => $cashFromCustomersGross,
                    'net_settlement_reference' => $netSettlementFromCustomers,
                    'by_payment_method' => $paymentMethodBreakdown,
                ],
                'cash_payments' => [
                    'to_suppliers' => $cashToSuppliers,
                    'supplier_breakdown' => $supplierBreakdown,
                    'payment_gateway_fees' => $gatewayFeesPaid,
                    'refunds_to_customers' => $refundsPaidToCustomers,
                    'refund_transactions' => $refundTransactions,
                    'operating_expenses' => $cashForOperatingExpenses,
                    'expense_breakdown' => $operatingExpensesByCategory,
                    'total_cash_payments' => $totalCashPayments,
                ],
                'net_cash_from_operating' => $netCashFromOperating,
            ],
            'investing_activities' => [
                'cash_outflows' => [
                    'purchase_of_assets' => $cashForInvestments,
                ],
                'cash_inflows' => [
                    'sale_of_assets' => $cashFromInvestments,
                ],
                'net_cash_from_investing' => $netCashFromInvesting,
            ],
            'financing_activities' => [
                'cash_inflows' => [
                    'loans_received' => $cashFromFinancing,
                ],
                'cash_outflows' => [
                    'loan_repayments' => $cashForFinancing,
                ],
                'net_cash_from_financing' => $netCashFromFinancing,
            ],
            'cash_summary' => [
                'net_cash_from_operating' => $netCashFromOperating,
                'net_cash_from_investing' => $netCashFromInvesting,
                'net_cash_from_financing' => $netCashFromFinancing,
                'net_increase_in_cash' => $netCashMovement,
            ],
            'cash_reconciliation' => [
                'opening_balance' => $openingCash,
                'opening_balance_date' => $openingBalanceContext['balance_date'],
                'opening_balance_notes' => $openingBalanceContext['notes'],
                'opening_balance_is_manual' => $openingBalanceContext['is_manual'],
                'net_movement' => $netCashMovement,
                'closing_balance' => $closingCash,
            ],
            'statistics' => [
                'total_transactions' => $receiptTransactionCount,
                'average_transaction_value' => $receiptTransactionCount > 0
                    ? $cashFromCustomersGross / $receiptTransactionCount
                    : 0,
            ],
        ];
    }

    private function normalizeDateTimeRange($startDate, $endDate): array
    {
        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }

    private function normalizeDateRange($startDate, $endDate): array
    {
        return [
            Carbon::parse($startDate)->toDateString(),
            Carbon::parse($endDate)->toDateString(),
        ];
    }

    private function resolveOpeningCashBalance(string $startDateOnly): array
    {
        if (!Schema::hasTable('cash_opening_balances')) {
            return [
                'amount' => 0.0,
                'balance_date' => null,
                'notes' => null,
                'is_manual' => false,
            ];
        }

        $balance = DB::table('cash_opening_balances')
            ->whereDate('balance_date', '<=', $startDateOnly)
            ->orderByDesc('balance_date')
            ->orderByDesc('id')
            ->first();

        return [
            'amount' => (float) ($balance->amount ?? 0),
            'balance_date' => $balance->balance_date ?? null,
            'notes' => $balance->notes ?? null,
            'is_manual' => $balance !== null,
        ];
    }

    private function buildGatewayFeeReport(iterable $orders, string $dateField): array
    {
        $feeOrders = collect($orders)
            ->filter(fn ($order) => (float) ($order->gateway_fee_amount ?? 0) > 0)
            ->values();

        $methods = $feeOrders
            ->groupBy(fn ($order) => (string) ($order->payment_method ?? 'unknown'))
            ->map(function ($methodOrders, $method) use ($dateField) {
                $channels = $methodOrders
                    ->groupBy(fn ($order) => (string) ($order->payment_channel ?? 'unknown'))
                    ->map(function ($channelOrders, $channel) use ($method, $dateField) {
                        $grossAmount = (float) $channelOrders->sum(fn ($order) => (float) ($order->base_amount ?? 0));
                        $feeAmount = (float) $channelOrders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));
                        $netAmount = (float) $channelOrders->sum(fn ($order) => (float) ($order->gateway_net_amount ?? 0));

                        return [
                            'payment_method' => $method,
                            'payment_channel' => $channel,
                            'method_label' => payment_method_label($method),
                            'channel_label' => payment_channel_label($channel === 'unknown' ? null : $channel),
                            'descriptor' => payment_descriptor($method, $channel === 'unknown' ? null : $channel),
                            'transaction_count' => $channelOrders->count(),
                            'gross_amount' => $grossAmount,
                            'fee_amount' => $feeAmount,
                            'net_amount' => $netAmount,
                            'average_fee_rate' => $grossAmount > 0 ? ($feeAmount / $grossAmount) * 100 : 0,
                            'source_summary' => $this->summarizeGatewayFeeSources($channelOrders),
                            'orders' => $channelOrders
                                ->sortByDesc($dateField)
                                ->map(function ($order) use ($dateField) {
                                    return [
                                        'order_id' => $order->id_order,
                                        'customer_name' => $order->customer_name,
                                        'status' => $order->status,
                                        'date' => $order->{$dateField} ?? $order->created_at ?? null,
                                        'gross_amount' => (float) ($order->base_amount ?? 0),
                                        'fee_amount' => (float) ($order->gateway_fee_amount ?? 0),
                                        'net_amount' => (float) ($order->gateway_net_amount ?? 0),
                                        'fee_source' => strtolower(trim((string) ($order->gateway_fee_source ?? 'unknown'))),
                                    ];
                                })
                                ->values()
                                ->all(),
                        ];
                    })
                    ->sortByDesc('fee_amount')
                    ->values()
                    ->all();

                $grossAmount = (float) $methodOrders->sum(fn ($order) => (float) ($order->base_amount ?? 0));
                $feeAmount = (float) $methodOrders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));
                $netAmount = (float) $methodOrders->sum(fn ($order) => (float) ($order->gateway_net_amount ?? 0));

                return [
                    'payment_method' => $method,
                    'method_label' => payment_method_label($method),
                    'transaction_count' => $methodOrders->count(),
                    'gross_amount' => $grossAmount,
                    'fee_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'average_fee_rate' => $grossAmount > 0 ? ($feeAmount / $grossAmount) * 100 : 0,
                    'source_summary' => $this->summarizeGatewayFeeSources($methodOrders),
                    'channels' => $channels,
                ];
            })
            ->sortByDesc('fee_amount')
            ->values()
            ->all();

        $grossAmount = (float) $feeOrders->sum(fn ($order) => (float) ($order->base_amount ?? 0));
        $feeAmount = (float) $feeOrders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));
        $netAmount = (float) $feeOrders->sum(fn ($order) => (float) ($order->gateway_net_amount ?? 0));

        return [
            'transaction_count' => $feeOrders->count(),
            'gross_amount' => $grossAmount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'average_fee_rate' => $grossAmount > 0 ? ($feeAmount / $grossAmount) * 100 : 0,
            'source_summary' => $this->summarizeGatewayFeeSources($feeOrders),
            'methods' => $methods,
        ];
    }

    private function summarizeGatewayFeeSources(iterable $orders): array
    {
        $counts = [
            'actual' => 0,
            'estimated' => 0,
            'unknown' => 0,
        ];

        foreach ($orders as $order) {
            $source = strtolower(trim((string) ($order->gateway_fee_source ?? 'unknown')));
            if (!array_key_exists($source, $counts)) {
                $source = 'unknown';
            }

            $counts[$source]++;
        }

        return array_filter($counts, fn ($count) => $count > 0);
    }
}
