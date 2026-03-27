<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_chat_session_memories', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_chat_session_memories', 'active_topic_key')) {
                $table->string('active_topic_key', 120)->nullable()->after('entities_json')->index();
            }

            if (!Schema::hasColumn('ai_chat_session_memories', 'topic_memories_json')) {
                $table->json('topic_memories_json')->nullable()->after('active_topic_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_chat_session_memories', function (Blueprint $table) {
            if (Schema::hasColumn('ai_chat_session_memories', 'topic_memories_json')) {
                $table->dropColumn('topic_memories_json');
            }

            if (Schema::hasColumn('ai_chat_session_memories', 'active_topic_key')) {
                $table->dropIndex(['active_topic_key']);
                $table->dropColumn('active_topic_key');
            }
        });
    }
};
