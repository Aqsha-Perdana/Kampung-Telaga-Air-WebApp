<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footage360', function (Blueprint $table) {
            $table->text('file_foto')->change();
            $table->text('file_lrv')->nullable()->change();
            $table->string('cloudinary_public_id')->nullable()->after('file_lrv');
            $table->string('cloudinary_public_id_lrv')->nullable()->after('cloudinary_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('footage360', function (Blueprint $table) {
            $table->dropColumn(['cloudinary_public_id', 'cloudinary_public_id_lrv']);
        });
    }
};