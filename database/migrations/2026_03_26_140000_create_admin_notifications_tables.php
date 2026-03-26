<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->string('type', 50)->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('order_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->json('package_names')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('MYR');
            $table->unsignedInteger('total_people')->default(0);
            $table->string('origin')->nullable();
            $table->ipAddress('source_ip')->nullable();
            $table->string('status')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('event_created_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('admin_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_notification_id')->constrained('admin_notifications')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['admin_notification_id', 'admin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
        Schema::dropIfExists('admin_notifications');
    }
};
