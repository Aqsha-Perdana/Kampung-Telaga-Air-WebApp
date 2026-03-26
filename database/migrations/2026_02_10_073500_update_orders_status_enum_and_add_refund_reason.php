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
        // 1. Add new columns
        Schema::table('orders', function (Blueprint $table) {
            $table->text('refund_reason')->nullable()->after('status');
            $table->text('refund_rejected_reason')->nullable()->after('refund_reason');
        });

        // 2. Modify ENUM status
        // DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'confirmed', 'cancelled', 'completed', 'refund_requested', 'refunded') NOT NULL DEFAULT 'pending'");
        // Using raw SQL for ENUM modification to be safe across different DB versions if needed, 
        // but Laravel schema builder doesn't support changing ENUM values easily without raw SQL.
        DB::statement("ALTER TABLE orders CHANGE COLUMN status status ENUM('pending', 'paid', 'confirmed', 'cancelled', 'completed', 'refund_requested', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert ENUM status
        DB::statement("UPDATE orders SET status = 'paid' WHERE status IN ('refund_requested', 'refunded')");
        DB::statement("ALTER TABLE orders CHANGE COLUMN status status ENUM('pending', 'paid', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'");

        // 2. Drop columns
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_reason', 'refund_rejected_reason']);
        });
    }
};
