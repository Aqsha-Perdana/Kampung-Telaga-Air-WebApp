<?php

namespace App\Services;

class PriceCalculator
{
    /**
     * Calculate selling price with a fixed pricing buffer.
     *
     * Formula:
     * selling_price = cost_price + target_profit + (cost_price * buffer_percentage)
     *
     * The buffer is used only for pricing protection and should not be treated
     * as the actual payment gateway fee in financial reports.
     *
     * @param float $costPrice Total cost from vendors
     * @param float $targetProfit Desired profit amount
     * @param string $paymentMethod Kept for backward compatibility with existing callers
     * @return array
     */
    public function calculateSellingPrice(float $costPrice, float $targetProfit, string $paymentMethod = 'stripe'): array
    {
        $bufferPercentage = package_fee_buffer_percentage();
        $estimatedFee = $costPrice * $bufferPercentage;

        $rawSellingPrice = $costPrice + $targetProfit + $estimatedFee;

        $sellingPrice = ceil($rawSellingPrice);

        $netProfit = $sellingPrice - $costPrice - $estimatedFee;

        return [
            'payment_method' => $paymentMethod,
            'buffer_percentage' => $bufferPercentage,
            'buffer_label' => package_fee_buffer_label(),
            'selling_price'  => (float) $sellingPrice,
            'estimated_fee'  => round($estimatedFee, 2),
            'net_profit'     => round($netProfit, 2),
            'raw_price'      => round($rawSellingPrice, 2),
        ];
    }
}
