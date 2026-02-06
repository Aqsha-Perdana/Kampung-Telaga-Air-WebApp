<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('culinaries', function (Blueprint $table) {
            $table->string('id_culinary', 10)->primary();
            $table->string('nama');
            $table->string('lokasi');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Tabel untuk paket kuliner (satu culinary bisa punya banyak paket)
        Schema::create('paket_culinaries', function (Blueprint $table) {
            $table->id();
            $table->string('id_culinary', 10);
            $table->string('nama_paket');
            $table->integer('kapasitas');
            $table->decimal('harga', 10, 2);
            $table->text('deskripsi_paket')->nullable();
            $table->timestamps();

            $table->foreign('id_culinary')
                  ->references('id_culinary')
                  ->on('culinaries')
                  ->onDelete('cascade');
        });

        // Tabel terpisah untuk foto (relasi one-to-many)
        Schema::create('foto_culinaries', function (Blueprint $table) {
            $table->id();
            $table->string('id_culinary', 10);
            $table->string('foto');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('id_culinary')
                  ->references('id_culinary')
                  ->on('culinaries')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_culinaries');
        Schema::dropIfExists('paket_culinaries');
        Schema::dropIfExists('culinaries');
    }
};