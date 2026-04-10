<?php

namespace App\Services\Sales;

use App\Services\Finance\OrderFinancialProjectionService;
use Illuminate\Support\Collection;

class SalesProfitCalculator
{
    public function __construct(private readonly OrderFinancialProjectionService $projectionService)
    {
    }

    public function buildGroupedOrders(Collection $orderItems): Collection
    {
        return $orderItems->groupBy('id_order')->map(function ($items) {
            $firstItem = $items->first();
            $gatewayAmounts = resolve_gateway_amounts(
                $firstItem->base_amount ?? 0,
                $firstItem->gateway_fee_amount ?? 0,
                $firstItem->gateway_net_amount ?? null
            );
            $firstItem->gateway_fee_amount = $gatewayAmounts['fee_amount'];
            $firstItem->gateway_net_amount = $gatewayAmounts['net_amount'];
            $projection = $this->projectionService->projectOrder($firstItem, $items);

            return (object) [
                'id_order' => $firstItem->id_order,
                'customer_name' => $firstItem->customer_name,
                'base_amount' => $firstItem->base_amount,
                'payment_amount' => $firstItem->payment_amount,
                'payment_currency' => $firstItem->payment_currency ?? 'MYR',
                'display_currency' => $firstItem->display_currency,
                'display_amount' => $firstItem->display_amount,
                'payment_method' => $firstItem->payment_method,
                'payment_channel' => $firstItem->payment_channel,
                'gateway_fee_amount' => $gatewayAmounts['fee_amount'],
                'gateway_net_amount' => $gatewayAmounts['net_amount'],
                'gateway_fee_source' => $firstItem->gateway_fee_source,
                'refund_fee' => $firstItem->refund_fee ?? 0,
                'status' => $firstItem->status,
                'created_at' => $firstItem->created_at,
                'items' => $items,
                'items_count' => $items->count(),
                'revenue_breakdown' => $projection['vendor_breakdown'],
                'original_profit' => (float) $projection['gross_profit'],
                'company_profit' => (float) $projection['net_profit_impact'],
                'package_names' => $items->pluck('nama_paket')->unique()->implode(', '),
            ];
        })->sortByDesc('created_at')->values();
    }

    public function calculateReportedProfit(string $status, float $originalProfit, float $gatewayFeeAmount, float $refundFee = 0): float
    {
        return $this->projectionService->calculateProfitImpactFromGrossProfit(
            $status,
            $originalProfit,
            $gatewayFeeAmount,
            $refundFee
        );
    }

    public function buildFinancialSummary(object $order, Collection $orderItems): array
    {
        return $this->projectionService->buildFinancialSummary($order, $orderItems);
    }
}
