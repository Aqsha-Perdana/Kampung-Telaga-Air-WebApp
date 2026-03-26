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
            $table->string('refund_status', 30)->nullable()->after('refund_fee');
            $table->string('stripe_refund_id')->nullable()->after('refund_status');
            $table->timestamp('refunded_at')->nullable()->after('stripe_refund_id');
            $table->text('refund_failure_reason')->nullable()->after('refunded_at');

            $table->index('refund_status', 'orders_refund_status_idx');
            $table->index('stripe_refund_id', 'orders_stripe_refund_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_refund_status_idx');
            $table->dropIndex('orders_stripe_refund_id_idx');
            $table->dropColumn([
                'refund_status',
                'stripe_refund_id',
                'refunded_at',
                'refund_failure_reason',
            ]);
        });
    }
};
