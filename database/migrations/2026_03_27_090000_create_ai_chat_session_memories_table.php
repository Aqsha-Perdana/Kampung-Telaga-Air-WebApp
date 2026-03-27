<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_session_memories', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->index();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('summary_text')->nullable();
            $table->json('topics_json')->nullable();
            $table->json('entities_json')->nullable();
            $table->string('latest_intent', 60)->nullable()->index();
            $table->json('latest_context_json')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['session_id', 'admin_id'], 'ai_chat_session_memories_session_admin_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_session_memories');
    }
};
