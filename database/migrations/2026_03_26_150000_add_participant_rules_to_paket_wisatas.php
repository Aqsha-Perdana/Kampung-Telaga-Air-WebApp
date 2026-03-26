<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_wisatas', function (Blueprint $table) {
            $table->unsignedInteger('minimum_participants')->default(1)->after('durasi_hari');
            $table->unsignedInteger('maximum_participants')->nullable()->after('minimum_participants');
        });
    }

    public function down(): void
    {
        Schema::table('paket_wisatas', function (Blueprint $table) {
            $table->dropColumn(['minimum_participants', 'maximum_participants']);
        });
    }
};
