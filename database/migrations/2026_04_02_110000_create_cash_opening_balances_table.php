<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->date('balance_date')->unique();
            $table->decimal('amount', 14, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('balance_date', 'cash_opening_balances_date_idx');
            $table->foreign('created_by')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_opening_balances', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex('cash_opening_balances_date_idx');
        });

        Schema::dropIfExists('cash_opening_balances');
    }
};
