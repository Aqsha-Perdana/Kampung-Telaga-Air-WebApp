<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tambah kolom baru saja (jangan rename)
            $table->string('display_currency', 3)->nullable()->after('currency');
            $table->decimal('display_amount', 12, 2)->nullable()->after('display_currency');
            $table->decimal('display_exchange_rate', 10, 6)->default(1)->after('display_amount');
            $table->string('payment_currency', 3)->default('MYR')->after('base_amount');
            $table->decimal('payment_amount', 12, 2)->default(0)->after('payment_currency');
        });
        
        // Update existing data
        DB::statement('UPDATE orders SET payment_currency = currency WHERE payment_currency = "MYR"');
        DB::statement('UPDATE orders SET payment_amount = total_amount WHERE payment_amount = 0');
        DB::statement('UPDATE orders SET display_currency = currency WHERE display_currency IS NULL');
        DB::statement('UPDATE orders SET display_amount = total_amount WHERE display_amount IS NULL');
        DB::statement('UPDATE orders SET display_exchange_rate = exchange_rate WHERE display_exchange_rate = 1');
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'display_currency',
                'display_amount', 
                'display_exchange_rate',
                'payment_currency',
                'payment_amount'
            ]);
        });
    }
};