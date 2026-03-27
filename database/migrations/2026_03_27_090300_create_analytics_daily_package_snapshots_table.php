<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_package_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->index();
            $table->string('package_id', 30)->index();
            $table->string('package_name');
            $table->string('package_status', 30)->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('total_participants')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->decimal('total_profit', 14, 2)->default(0);
            $table->decimal('margin_percent', 8, 2)->default(0);
            $table->json('snapshot_json')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_date', 'package_id'], 'analytics_daily_package_snapshots_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_package_snapshots');
    }
};
