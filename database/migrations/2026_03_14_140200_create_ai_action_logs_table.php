<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('source_channel', 30)->index(); // composer|chat
            $table->string('action_key', 80)->index();
            $table->string('target_type', 60)->nullable();
            $table->string('target_id', 80)->nullable();
            $table->json('payload_json')->nullable();
            $table->string('confirmation_state', 30)->default('suggested')->index(); // suggested|confirmed|executed|failed
            $table->text('result_message')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['source_channel', 'confirmation_state']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_action_logs');
    }
};