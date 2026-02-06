<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update base_amount structure
        Schema::table('orders', function (Blueprint $table) {
            // Change base_amount to NOT NULL with default 0
            $table->decimal('base_amount', 10, 2)->nullable(false)->default(0)->change();
        });

        // Update existing data: Set base_amount to harga asli dalam RM
        DB::statement("
            UPDATE orders 
            SET base_amount = CASE 
                WHEN currency = 'MYR' OR currency IS NULL THEN total_amount
                WHEN exchange_rate > 0 THEN ROUND(total_amount / exchange_rate, 2)
                ELSE total_amount
            END
            WHERE base_amount IS NULL OR base_amount = 0
        ");

        // Set default exchange_rate to 1 for MYR if NULL
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('base_amount', 10, 2)->nullable()->change();
        });
    }
};