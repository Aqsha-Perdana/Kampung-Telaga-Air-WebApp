<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosks', function (Blueprint $table) {
            $table->string('id_kiosk', 10)->primary();
            $table->string('nama');
            $table->integer('kapasitas');
            $table->decimal('harga_per_paket', 10, 2);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Tabel terpisah untuk foto (relasi one-to-many)
        Schema::create('foto_kiosks', function (Blueprint $table) {
            $table->id();
            $table->string('id_kiosk', 10);
            $table->string('foto');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('id_kiosk')
                  ->references('id_kiosk')
                  ->on('kiosks')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_kiosks');
        Schema::dropIfExists('kiosks');
    }
};