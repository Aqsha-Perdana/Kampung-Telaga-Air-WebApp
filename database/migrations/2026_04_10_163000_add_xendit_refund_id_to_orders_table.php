<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('xendit_refund_id')->nullable()->after('stripe_refund_id');
            $table->index('xendit_refund_id', 'orders_xendit_refund_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_xendit_refund_id_idx');
            $table->dropColumn('xendit_refund_id');
        });
    }
};
