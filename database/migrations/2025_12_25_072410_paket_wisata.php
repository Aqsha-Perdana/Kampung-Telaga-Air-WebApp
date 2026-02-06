<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_wisatas', function (Blueprint $table) {
            $table->string('id_paket', 10)->primary();
            $table->string('nama_paket');
            $table->integer('durasi_hari');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_total', 12, 2)->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // Tabel pivot untuk destinasi
        Schema::create('paket_wisata_destinasi', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->string('id_destinasi', 10);
            $table->integer('hari_ke')->default(1);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
            $table->foreign('id_destinasi')->references('id_destinasi')->on('destinasis')->onDelete('cascade');
        });

        // Tabel pivot untuk homestay
        Schema::create('paket_wisata_homestay', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->string('id_homestay', 255);
            $table->integer('jumlah_malam')->default(1);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
        });

        // Tabel pivot untuk culinary (paket culinary)
        Schema::create('paket_wisata_culinary', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->unsignedBigInteger('id_paket_culinary');
            $table->integer('hari_ke')->default(1);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
            $table->foreign('id_paket_culinary')->references('id')->on('paket_culinaries')->onDelete('cascade');
        });

        // Tabel pivot untuk boat
        Schema::create('paket_wisata_boat', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->unsignedBigInteger('id_boat');
            $table->integer('hari_ke')->default(1);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
        });

        // Tabel pivot untuk kiosk
        Schema::create('paket_wisata_kiosk', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->string('id_kiosk', 10);
            $table->integer('hari_ke')->default(1);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
            $table->foreign('id_kiosk')->references('id_kiosk')->on('kiosks')->onDelete('cascade');
        });

        // Tabel untuk itinerary per hari
        Schema::create('paket_wisata_itineraries', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket', 10);
            $table->integer('hari_ke');
            $table->string('judul_hari');
            $table->text('deskripsi_kegiatan');
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_wisata_itineraries');
        Schema::dropIfExists('paket_wisata_kiosk');
        Schema::dropIfExists('paket_wisata_boat');
        Schema::dropIfExists('paket_wisata_culinary');
        Schema::dropIfExists('paket_wisata_homestay');
        Schema::dropIfExists('paket_wisata_destinasi');
        Schema::dropIfExists('paket_wisatas');
    }
};