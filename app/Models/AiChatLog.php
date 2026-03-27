<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'admin_id',
        'role',
        'message',
        'intent',
        'context_json',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'context_json' => 'array',
    ];
}
