<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix existing orders where base_amount is NULL
     * CheckoutController already saves correctly, this is just for old data
     */
    public function up(): void
    {
        // Update existing data where base_amount is NULL
        // For MYR orders: base_amount = total_amount
        // For other currencies: base_amount = total_amount / exchange_rate
        DB::statement("
            UPDATE orders 
            SET base_amount = CASE 
                WHEN currency = 'MYR' OR currency IS NULL THEN total_amount
                WHEN exchange_rate > 0 THEN ROUND(total_amount / exchange_rate, 2)
                ELSE total_amount
            END
            WHERE base_amount IS NULL OR base_amount = 0
        ");

        // Set default exchange_rate to 1 for MYR orders
        DB::statement("
            UPDATE orders 
            SET exchange_rate = 1.000000
            WHERE (exchange_rate IS NULL OR exchange_rate = 0) 
            AND (currency = 'MYR' OR currency IS NULL)
        ");

        // Set default currency to MYR if NULL
        DB::statement("
            UPDATE orders 
            SET currency = 'MYR'
            WHERE currency IS NULL
        ");
        
        \Log::info('Migration completed: Fixed ' . DB::table('orders')->whereNotNull('base_amount')->count() . ' orders');
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