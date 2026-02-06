<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinasis', function (Blueprint $table) {
            $table->string('id_destinasi', 10)->primary();
            $table->string('nama');
            $table->string('lokasi');
            $table->text('deskripsi');
            $table->timestamps();
        });

        // Tabel terpisah untuk foto (relasi one-to-many)
        Schema::create('foto_destinasis', function (Blueprint $table) {
            $table->id();
            $table->string('id_destinasi', 10);
            $table->string('foto');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('id_destinasi')
                  ->references('id_destinasi')
                  ->on('destinasis')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_destinasis');
        Schema::dropIfExists('destinasis');
    }
};