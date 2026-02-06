<?php
// database/migrations/xxxx_xx_xx_add_revenue_tracking_to_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan kolom tracking jika belum ada
        if (!Schema::hasColumn('orders', 'resource_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('resource_type')->nullable()->after('id_order');
                $table->string('resource_id')->nullable()->after('resource_type');
            });
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['resource_type', 'resource_id']);
        });
    }
};