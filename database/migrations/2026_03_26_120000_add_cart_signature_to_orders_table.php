<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cart_signature', 64)->nullable()->after('customer_address');
            $table->index(['user_id', 'status', 'cart_signature'], 'orders_user_status_cart_signature_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_cart_signature_idx');
            $table->dropColumn('cart_signature');
        });
    }
};
