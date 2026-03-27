<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatSessionMemory extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'admin_id',
        'summary_text',
        'topics_json',
        'entities_json',
        'active_topic_key',
        'topic_memories_json',
        'latest_intent',
        'latest_context_json',
        'message_count',
        'last_activity_at',
    ];

    protected $casts = [
        'topics_json' => 'array',
        'entities_json' => 'array',
        'topic_memories_json' => 'array',
        'latest_context_json' => 'array',
        'last_activity_at' => 'datetime',
    ];
}
