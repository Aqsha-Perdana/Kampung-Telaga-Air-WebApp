<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add currency conversion fields
            $table->decimal('base_amount', 10, 2)->nullable()->after('total_amount')
                ->comment('Original amount in MYR');
            $table->decimal('exchange_rate', 10, 6)->default(1)->after('currency')
                ->comment('Exchange rate used for conversion');
            
            // Modify payment_method to support multiple types
            $table->string('payment_method', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['base_amount', 'exchange_rate']);
        });
    }
};