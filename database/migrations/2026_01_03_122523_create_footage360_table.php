<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footage360', function (Blueprint $table) {
    $table->id('id_footage360');
    $table->string('id_destinasi', 10); // HARUS string
    $table->string('judul');
    $table->text('deskripsi')->nullable();
    $table->string('file_foto');
    $table->string('file_lrv')->nullable();
    $table->integer('urutan')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->foreign('id_destinasi')
          ->references('id_destinasi')
          ->on('destinasis')
          ->onDelete('cascade');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('footage360');
    }
};