<?php

return [
    'api_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
    'temperature' => (float) env('GROQ_TEMPERATURE', 0.2),
    'max_tokens' => (int) env('GROQ_MAX_TOKENS', 300),
    'timeout_seconds' => (int) env('GROQ_TIMEOUT', 12),

    'history' => [
        'max_messages' => (int) env('CHATBOT_HISTORY_MAX_MESSAGES', 4),
    ],

    'knowledge' => [
        'cache_minutes' => (int) env('CHATBOT_KNOWLEDGE_CACHE_MINUTES', 5),
        'max_items_per_section' => (int) env('CHATBOT_KNOWLEDGE_MAX_ITEMS', 5),
        'max_description_chars' => (int) env('CHATBOT_KNOWLEDGE_MAX_DESC', 70),
    ],
];
