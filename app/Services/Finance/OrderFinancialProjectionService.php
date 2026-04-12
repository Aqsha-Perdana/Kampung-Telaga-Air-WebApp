<?php

namespace App\Services\Finance;

use App\Services\FinancialReport\OrderCostCalculator;
use Illuminate\Support\Collection;

class OrderFinancialProjectionService
{
    public function __construct(private readonly OrderCostCalculator $costCalculator)
    {
    }

    public function operatingStatuses(): array
    {
        return ['paid', 'confirmed', 'completed'];
    }

    public function recognizedStatuses(): array
    {
        return ['paid', 'confirmed', 'completed', 'refunded'];
    }

    public function normalizeOrders(Collection $orders): Collection
    {
        return $orders->map(function ($order) {
            $gatewayAmounts = resolve_gateway_amounts(
                $order->base_amount ?? 0,
                $order->gateway_fee_amount ?? 0,
                $order->gateway_net_amount ?? null
            );

            $order->gateway_fee_amount = $gatewayAmounts['fee_amount'];
            $order->gateway_net_amount = $gatewayAmounts['net_amount'];

            return $order;
        });
    }

    public function projectOrder(object $order, Collection $orderItems): array
    {
        $gatewayFee = (float) ($order->gateway_fee_amount ?? 0);
        $baseAmount = (float) ($order->base_amount ?? 0);

        if (($order->status ?? null) === 'refunded') {
            return [
                'order_id' => $order->id_order,
                'customer' => $order->customer_name,
                'date' => $order->created_at ?? null,
                'status' => $order->status,
                'gross_revenue' => 0.0,
                'sales_discount' => 0.0,
                'net_revenue' => 0.0,
                'revenue' => 0.0,
                'cost_of_sales' => 0.0,
                'gross_profit' => 0.0,
                'gateway_fee' => $gatewayFee,
                'gateway_net_amount' => (float) ($order->gateway_net_amount ?? max(0, $baseAmount - $gatewayFee)),
                'payment_method' => $order->payment_method ?? null,
                'payment_channel' => $order->payment_channel ?? null,
                'gateway_fee_source' => $order->gateway_fee_source ?? null,
                'currency_info' => [
                    'payment_currency' => 'MYR',
                    'display_currency' => $order->display_currency ?? null,
                    'display_amount' => $order->display_amount ?? null,
                ],
                'other_income' => (float) ($order->refund_fee ?? 0),
                'net_profit_impact' => $this->calculateProfitImpactFromGrossProfit(
                    (string) ($order->status ?? ''),
                    0.0,
                    $gatewayFee,
                    (float) ($order->refund_fee ?? 0)
                ),
                'vendor_breakdown' => $this->emptyVendorBreakdown(),
                'cost_breakdown' => $this->emptyCostBreakdown(),
            ];
        }

        $orderCost = $this->costCalculator->calculateOrderCost((string) $order->id_order);
        $grossRevenue = (float) $orderItems->sum(function ($item) {
            return (float) ($item->original_subtotal ?? $item->subtotal ?? 0);
        });
        $salesDiscount = (float) $orderItems->sum(function ($item) {
            return (float) ($item->discount_amount ?? 0);
        });
        $netRevenue = $baseAmount;

        if ($grossRevenue <= 0.0) {
            $grossRevenue = $netRevenue;
            $salesDiscount = 0.0;
        }

        $costOfSales = (float) ($orderCost['total'] ?? 0);
        $grossProfit = $netRevenue - $costOfSales;

        return [
            'order_id' => $order->id_order,
            'customer' => $order->customer_name,
            'date' => $order->created_at ?? null,
            'status' => $order->status,
            'gross_revenue' => $grossRevenue,
            'sales_discount' => $salesDiscount,
            'net_revenue' => $netRevenue,
            'revenue' => $netRevenue,
            'cost_of_sales' => $costOfSales,
            'gross_profit' => $grossProfit,
            'gateway_fee' => $gatewayFee,
            'gateway_net_amount' => (float) ($order->gateway_net_amount ?? max(0, $netRevenue - $gatewayFee)),
            'payment_method' => $order->payment_method ?? null,
            'payment_channel' => $order->payment_channel ?? null,
            'gateway_fee_source' => $order->gateway_fee_source ?? null,
            'currency_info' => [
                'payment_currency' => 'MYR',
                'display_currency' => $order->display_currency ?? null,
                'display_amount' => $order->display_amount ?? null,
            ],
            'other_income' => 0.0,
            'net_profit_impact' => $this->calculateProfitImpactFromGrossProfit(
                (string) ($order->status ?? ''),
                $grossProfit,
                $gatewayFee,
                0.0
            ),
            'vendor_breakdown' => [
                'boat_total' => (float) data_get($orderCost, 'breakdown.boats.total', 0),
                'homestay_total' => (float) data_get($orderCost, 'breakdown.homestays.total', 0),
                'culinary_total' => (float) data_get($orderCost, 'breakdown.culinary.total', 0),
                'kiosk_total' => (float) data_get($orderCost, 'breakdown.kiosks.total', 0),
            ],
            'cost_breakdown' => $orderCost['breakdown'] ?? $this->emptyCostBreakdown(),
        ];
    }

    public function buildFinancialSummary(object $order, Collection $orderItems): array
    {
        $projection = $this->projectOrder($order, $orderItems);
        $isRefunded = ($order->status ?? null) === 'refunded';
        $grossCustomerPayment = (float) ($order->base_amount ?? 0);
        $gatewayFee = (float) $projection['gateway_fee'];
        $netSettlement = (float) $projection['gateway_net_amount'];
        $recognizedRevenue = (float) $projection['net_revenue'];
        $vendorCost = (float) $projection['cost_of_sales'];
        $grossProfit = (float) $projection['gross_profit'];
        $refundFeeRetained = (float) ($projection['other_income'] ?? 0);
        $reportedProfitImpact = (float) $projection['net_profit_impact'];
        $refundAmountReturned = (float) ($order->refund_amount ?? 0);

        return [
            'vendor_total' => $vendorCost,
            'original_profit' => $grossProfit,
            'gateway_fee' => $gatewayFee,
            'gateway_net_amount' => $netSettlement,
            'reported_profit_impact' => $reportedProfitImpact,
            'settlement' => [
                'gross_customer_payment' => $grossCustomerPayment,
                'gateway_fee' => $gatewayFee,
                'net_settlement_received' => $netSettlement,
                'refund_amount_returned' => $refundAmountReturned,
                'formula' => 'Net settlement = total paid by guest - gateway fee (MDR).',
                'note' => $isRefunded
                    ? 'This is the gateway settlement snapshot when payment was captured, before any refund was sent back to the guest.'
                    : 'This is the amount the business receives from the payment gateway after MDR is deducted.',
            ],
            'reporting' => [
                'recognized_revenue' => $recognizedRevenue,
                'vendor_cost_snapshot' => $vendorCost,
                'gross_profit_before_gateway_fee' => $grossProfit,
                'refund_fee_retained' => $refundFeeRetained,
                'reported_profit_impact' => $reportedProfitImpact,
                'impact_label' => $isRefunded
                    ? 'Reported Profit Impact in Financial Reports'
                    : 'Profit After Gateway Fee (MDR)',
                'formula' => $isRefunded
                    ? 'For refunded orders, reported impact = retained refund fee - gateway fee (MDR).'
                    : 'Profit after gateway fee = gross profit before gateway fee - gateway fee (MDR).',
                'note' => $isRefunded
                    ? 'Refunded orders no longer recognize sales revenue. Financial reports only keep the retained refund fee as the remaining impact.'
                    : 'This section follows the same profit logic used in Sales Record and Financial Reports.',
            ],
        ];
    }

    public function calculateProfitImpactFromGrossProfit(string $status, float $grossProfit, float $gatewayFee, float $otherIncome = 0.0): float
    {
        return $status === 'refunded'
            ? $otherIncome - $gatewayFee
            : $grossProfit - $gatewayFee + $otherIncome;
    }

    private function emptyVendorBreakdown(): array
    {
        return [
            'boat_total' => 0.0,
            'homestay_total' => 0.0,
            'culinary_total' => 0.0,
            'kiosk_total' => 0.0,
        ];
    }

    private function emptyCostBreakdown(): array
    {
        return [
            'boats' => ['items' => [], 'total' => 0.0],
            'homestays' => ['items' => [], 'total' => 0.0],
            'culinary' => ['items' => [], 'total' => 0.0],
            'kiosks' => ['items' => [], 'total' => 0.0],
        ];
    }
}
