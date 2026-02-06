<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beban_operasionals', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal');
            $table->enum('kategori', [
                'Gaji dan Upah',
                'Listrik',
                'Air',
                'Telepon dan Internet',
                'Sewa Gedung',
                'Perlengkapan Kantor',
                'Transportasi',
                'Pemeliharaan',
                'Asuransi',
                'Pajak',
                'Iklan dan Promosi',
                'Lain-lain'
            ]);
            $table->string('deskripsi');
            $table->decimal('jumlah', 15, 2);
            $table->enum('metode_pembayaran', ['Tunai', 'Transfer', 'Kartu Kredit', 'Kartu Debit', 'Cek']);
            $table->string('nomor_referensi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beban_operasionals');
    }
};