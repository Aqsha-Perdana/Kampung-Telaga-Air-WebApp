<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Cart (Keranjang)
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // Untuk guest user
            $table->unsignedBigInteger('user_id')->nullable(); // Untuk logged user
            $table->string('id_paket', 10);
            $table->integer('jumlah_peserta');
            $table->date('tanggal_keberangkatan');
            $table->text('catatan')->nullable();
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
        });

        // Tabel Orders (Pesanan)
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id_order', 20)->primary(); // ORD-YYYYMMDD-XXXXX
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Data Customer
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            
            // Order Info
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('MYR'); // MYR, USD, IDR, etc
            $table->enum('status', ['pending', 'paid', 'confirmed', 'cancelled', 'completed'])->default('pending');
            
            // Payment Info
            $table->string('payment_method')->nullable(); // stripe, bank_transfer
            $table->string('payment_intent_id')->nullable(); // Stripe Payment Intent ID
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
        });

        // Tabel Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('id_order', 20);
            $table->string('id_paket', 10);
            $table->string('nama_paket');
            $table->integer('durasi_hari');
            $table->integer('jumlah_peserta');
            $table->date('tanggal_keberangkatan');
            $table->text('catatan')->nullable();
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('id_order')->references('id_order')->on('orders')->onDelete('cascade');
            $table->foreign('id_paket')->references('id_paket')->on('paket_wisatas')->onDelete('cascade');
        });

        // Tabel Payment Logs
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('id_order', 20);
            $table->string('payment_intent_id')->nullable();
            $table->string('payment_method');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->text('response_data')->nullable(); // JSON response dari Stripe
            $table->timestamps();

            $table->foreign('id_order')->references('id_order')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
    }
};