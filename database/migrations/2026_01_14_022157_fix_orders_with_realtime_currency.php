<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Services\ExchangeRateService;

return new class extends Migration
{
    public function up(): void
    {
        $exchangeService = new ExchangeRateService();
        
        echo "🔄 Fixing existing orders with real-time rates...\n\n";

        $orders = DB::table('orders')->get();
        
        foreach ($orders as $order) {
            $currency = $order->currency ?? 'MYR';
            $totalAmount = $order->total_amount;
            $currentRate = $order->exchange_rate;
            
            // Get current real-time rate
            $realTimeRate = $exchangeService->getRate($currency);
            
            // Calculate base_amount (harga asli RM)
            if ($currency == 'MYR') {
                $baseAmount = $totalAmount;
                $useRate = 1.0;
            } else {
                // Customer paid X in their currency
                // base_amount = total_amount / exchange_rate
                
                // If old rate exists and looks correct, use it (historical rate)
                if ($currentRate > 0 && $currentRate != 1) {
                    $useRate = $currentRate;
                } else {
                    // Use real-time rate for old data
                    $useRate = $realTimeRate;
                }
                
                $baseAmount = $totalAmount / $useRate;
            }
            
            DB::table('orders')
                ->where('id_order', $order->id_order)
                ->update([
                    'base_amount' => round($baseAmount, 2),
                    'exchange_rate' => $useRate
                ]);
            
            echo "✅ {$order->id_order}: {$currency} {$totalAmount} → RM " . round($baseAmount, 2) . " (rate: {$useRate})\n";
        }
        
        echo "\n✅ Migration completed!\n";
    }

    public function down(): void
    {
        echo "⚠️  Cannot reverse data changes\n";
    }
};