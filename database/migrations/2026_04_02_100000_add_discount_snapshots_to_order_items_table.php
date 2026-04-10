<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_subtotal', 12, 2)->nullable()->after('subtotal');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('original_subtotal');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_amount');
            $table->string('discount_type', 20)->nullable()->after('discount_percentage');
        });

        DB::table('order_items')->update([
            'original_subtotal' => DB::raw('subtotal'),
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'discount_type' => 'none',
        ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'original_subtotal',
                'discount_amount',
                'discount_percentage',
                'discount_type',
            ]);
        });
    }
};
