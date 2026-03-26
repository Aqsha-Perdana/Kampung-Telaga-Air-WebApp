<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix existing orders where base_amount is NULL or 0
     * CheckoutController already saves correctly, this is just for old data
     */
    public function up(): void
    {
        \Log::info('Starting migration to fix base_amount...');
        
        // Count orders that need fixing
        $needsFixing = DB::table('orders')
            ->where(function($query) {
                $query->whereNull('base_amount')
                      ->orWhere('base_amount', 0);
            })
            ->count();
            
        \Log::info("Found {$needsFixing} orders with base_amount NULL or 0");

        // Update existing data where base_amount is NULL or 0
        // For MYR orders: base_amount = total_amount
        // For other currencies: base_amount = total_amount / exchange_rate
        $updated = DB::statement("
            UPDATE orders 
            SET base_amount = CASE 
                WHEN currency = 'MYR' OR currency IS NULL THEN total_amount
                WHEN exchange_rate > 1 THEN ROUND(total_amount / exchange_rate, 2)
                WHEN exchange_rate > 0 AND exchange_rate < 1 THEN ROUND(total_amount / exchange_rate, 2)
                ELSE total_amount
            END
            WHERE base_amount IS NULL OR base_amount = 0 OR base_amount = 0.00
        ");

        \Log::info("Update query executed: " . ($updated ? 'Success' : 'Failed'));

        // Set default exchange_rate to 1 for MYR orders that have NULL or 0 exchange_rate
        DB::statement("
            UPDATE orders 
            SET exchange_rate = 1.000000
            WHERE (exchange_rate IS NULL OR exchange_rate = 0 OR exchange_rate = 1) 
            AND (currency = 'MYR' OR currency IS NULL)
        ");

        // Set default currency to MYR if NULL
        DB::statement("
            UPDATE orders 
            SET currency = 'MYR'
            WHERE currency IS NULL OR currency = ''
        ");
        
        // Log hasil
        $fixed = DB::table('orders')
            ->where('base_amount', '>', 0)
            ->count();
            
        \Log::info("Migration completed: {$fixed} orders now have base_amount set");
        
        // Log orders yang masih bermasalah
        $stillBroken = DB::table('orders')
            ->where(function($query) {
                $query->whereNull('base_amount')
                      ->orWhere('base_amount', 0);
            })
            ->get(['id_order', 'total_amount', 'base_amount', 'currency', 'exchange_rate']);
            
        if ($stillBroken->count() > 0) {
            \Log::warning("Still {$stillBroken->count()} orders with base_amount = 0:");
            foreach ($stillBroken as $order) {
                \Log::warning("Order {$order->id_order}: total={$order->total_amount}, base={$order->base_amount}, curr={$order->currency}, rate={$order->exchange_rate}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse data updates
        \Log::warning('Cannot reverse base_amount data updates');
    }
};