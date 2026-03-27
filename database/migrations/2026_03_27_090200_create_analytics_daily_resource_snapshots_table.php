<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_resource_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->index();
            $table->string('resource_type', 40)->index();
            $table->unsignedInteger('total_resources')->default(0);
            $table->unsignedInteger('booked_resources')->default(0);
            $table->unsignedInteger('active_capacity')->default(0);
            $table->decimal('utilization_percent', 8, 2)->default(0);
            $table->json('snapshot_json')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_date', 'resource_type'], 'analytics_daily_resource_snapshots_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_resource_snapshots');
    }
};
