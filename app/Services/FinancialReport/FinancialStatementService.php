<?php

namespace App\Services\FinancialReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    public function __construct(private readonly OrderCostCalculator $costCalculator)
    {
    }

    public function getProfitLoss($startDate, $endDate): array
    {
        [$startAt, $endAt] = $this->normalizeDateTimeRange($startDate, $endDate);
        [$startDateOnly, $endDateOnly] = $this->normalizeDateRange($startDate, $endDate);

        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('created_at', [$startAt, $endAt])
            ->get();

        $operatingOrders = $orders->whereIn('status', ['paid', 'confirmed', 'completed']);

        $refundFeeIncome = $orders
            ->where('status', 'refunded')
            ->sum(fn ($order) => (float) ($order->refund_fee ?? 0));

        $revenue = $operatingOrders->sum(fn ($order) => (float) $order->base_amount);
        $paymentGatewayFees = $orders->sum(fn ($order) => (float) ($order->gateway_fee_amount ?? 0));

        $costOfSales = 0.0;
        $revenueBreakdown = [];

        foreach ($orders as $order) {
            if ($order->status === 'refunded') {
                $itemRevenue = 0.0;
                $itemCost = 0.0;
                $itemOtherIncome = (float) ($order->refund_fee ?? 0);
                $itemGatewayFee = (float) ($order->gateway_fee_amount ?? 0);
                $itemBreakdown = [];
            } else {
                $orderCost = $this->costCalculator->calculateOrderCost((string) $order->id_order);
                $itemRevenue = (float) $order->base_amount;
                $itemCost = (float) $orderCost['total'];
                $itemOtherIncome = 0.0;
                $itemGatewayFee = (float) ($order->gateway_fee_amount ?? 0);
                $itemBreakdown = $orderCost['breakdown'];
            }

            $costOfSales += $itemCost;

            $revenueBreakdown[] = [
                'order_id' => $order->id_order,
                'customer' => $order->customer_name,
                'date' => $order->created_at,
                'revenue' => $itemRevenue,
                'status' => $order->status,
                'currency_info' => [
                    'payment_currency' => 'MYR',
                    'display_currency' => $order->display_currency,
                    'display_amount' => $order->display_amount,
                ],
                'cost_of_sales' => $itemCost,
                'gross_profit' => $itemRevenue - $itemCost,
                'gateway_fee' => $itemGatewayFee,
                'gateway_net_amount' => (float) ($order->gateway_net_amount ?? ($itemRevenue - $itemGatewayFee)),
                'payment_channel' => $order->payment_channel,
                'other_income' => $itemOtherIncome,
                'net_profit_impact' => ($itemRevenue - $itemCost - $itemGatewayFee) + $itemOtherIncome,
                'cost_breakdown' => $itemBreakdown,
            ];
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

        $expensesByNature['Payment Gateway Fees'] = [
            'count' => $orders->filter(fn ($order) => (float) ($order->gateway_fee_amount ?? 0) > 0)->count(),
            'amount' => $paymentGatewayFees,
        ];

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
                'tour_package_sales' => $revenue,
                'other_revenue' => 0,
                'total_revenue' => $revenue,
            ],
            'cost_of_sales' => [
                'boat_services' => $this->costCalculator->getCostByType($operatingOrders, 'boats'),
                'homestay_services' => $this->costCalculator->getCostByType($operatingOrders, 'homestays'),
                'culinary_services' => $this->costCalculator->getCostByType($operatingOrders, 'culinary'),
                'kiosk_services' => $this->costCalculator->getCostByType($operatingOrders, 'kiosks'),
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

        $cashFromCustomersGross = (float) DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->sum('base_amount');

        $gatewayFeesWithheld = (float) DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->sum(DB::raw('COALESCE(gateway_fee_amount, 0)'));

        $cashFromCustomersNet = (float) DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->sum(DB::raw('COALESCE(gateway_net_amount, base_amount - COALESCE(gateway_fee_amount, 0))'));

        $paymentMethodBreakdown = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(base_amount) as gross_amount'),
                DB::raw('SUM(COALESCE(gateway_fee_amount, 0)) as fee_amount'),
                DB::raw('SUM(COALESCE(gateway_net_amount, base_amount - COALESCE(gateway_fee_amount, 0))) as net_amount')
            )
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->payment_method => [
                        'count' => $item->transaction_count,
                        'gross_amount' => (float) $item->gross_amount,
                        'fee_amount' => (float) $item->fee_amount,
                        'net_amount' => (float) $item->net_amount,
                    ],
                ];
            })
            ->toArray();

        $receiptTransactionCount = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->count();

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

        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->get();

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

        $netCashFromOperating = $cashFromCustomersNet
            - $refundsPaidToCustomers
            - $cashToSuppliers
            - $cashForOperatingExpenses;

        $cashForInvestments = 0.0;
        $cashFromInvestments = 0.0;
        $netCashFromInvesting = $cashFromInvestments - $cashForInvestments;

        $cashFromFinancing = 0.0;
        $cashForFinancing = 0.0;
        $netCashFromFinancing = $cashFromFinancing - $cashForFinancing;

        $netCashMovement = $netCashFromOperating + $netCashFromInvesting + $netCashFromFinancing;

        $openingCash = 0.0;
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
                    'payment_gateway_fees_withheld' => $gatewayFeesWithheld,
                    'from_customers' => $cashFromCustomersNet,
                    'by_payment_method' => $paymentMethodBreakdown,
                ],
                'cash_payments' => [
                    'to_suppliers' => $cashToSuppliers,
                    'supplier_breakdown' => $supplierBreakdown,
                    'refunds_to_customers' => $refundsPaidToCustomers,
                    'refund_transactions' => $refundTransactions,
                    'operating_expenses' => $cashForOperatingExpenses,
                    'expense_breakdown' => $operatingExpensesByCategory,
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
                'net_movement' => $netCashMovement,
                'closing_balance' => $closingCash,
            ],
            'statistics' => [
                'total_transactions' => $receiptTransactionCount,
                'average_transaction_value' => $receiptTransactionCount > 0
                    ? $cashFromCustomersNet / $receiptTransactionCount
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
}
