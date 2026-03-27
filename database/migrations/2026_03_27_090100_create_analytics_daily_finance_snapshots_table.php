<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_finance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->decimal('revenue', 14, 2)->default(0);
            $table->decimal('cost_of_sales', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->decimal('operating_expenses', 14, 2)->default(0);
            $table->decimal('net_profit', 14, 2)->default(0);
            $table->decimal('gross_margin_percent', 8, 2)->default(0);
            $table->decimal('net_margin_percent', 8, 2)->default(0);
            $table->decimal('refund_fee_income', 14, 2)->default(0);
            $table->decimal('net_cash_movement', 14, 2)->default(0);
            $table->string('top_expense_category')->nullable();
            $table->json('expense_breakdown_json')->nullable();
            $table->json('snapshot_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_finance_snapshots');
    }
};
