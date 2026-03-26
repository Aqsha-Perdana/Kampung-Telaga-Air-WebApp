<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('boat_cost_total', 12, 2)->nullable()->after('subtotal');
            $table->decimal('homestay_cost_total', 12, 2)->nullable()->after('boat_cost_total');
            $table->decimal('culinary_cost_total', 12, 2)->nullable()->after('homestay_cost_total');
            $table->decimal('kiosk_cost_total', 12, 2)->nullable()->after('culinary_cost_total');
            $table->decimal('vendor_cost_total', 12, 2)->nullable()->after('kiosk_cost_total');
            $table->decimal('company_profit_total', 12, 2)->nullable()->after('vendor_cost_total');
            $table->json('boat_cost_items')->nullable()->after('company_profit_total');
            $table->json('homestay_cost_items')->nullable()->after('boat_cost_items');
            $table->json('culinary_cost_items')->nullable()->after('homestay_cost_items');
            $table->json('kiosk_cost_items')->nullable()->after('culinary_cost_items');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'boat_cost_total',
                'homestay_cost_total',
                'culinary_cost_total',
                'kiosk_cost_total',
                'vendor_cost_total',
                'company_profit_total',
                'boat_cost_items',
                'homestay_cost_items',
                'culinary_cost_items',
                'kiosk_cost_items',
            ]);
        });
    }
};
