<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_wisatas', function (Blueprint $table) {
            
            
            // Tambah kolom baru
            $table->decimal('harga_jual', 12, 2)->default(0)->after('harga_total');
            $table->decimal('diskon_nominal', 12, 2)->default(0)->after('harga_jual');
            $table->decimal('diskon_persen', 5, 2)->default(0)->after('diskon_nominal');
            $table->enum('tipe_diskon', ['nominal', 'persen', 'none'])->default('none')->after('diskon_persen');
            $table->decimal('harga_final', 12, 2)->default(0)->after('tipe_diskon');
        });
    }

    public function down(): void
    {
        Schema::table('paket_wisatas', function (Blueprint $table) {
            $table->renameColumn('harga_modal', 'harga_total');
            $table->dropColumn([
                'harga_jual', 
                'diskon_nominal', 
                'diskon_persen', 
                'tipe_diskon',
                'harga_final'
            ]);
        });
    }
};