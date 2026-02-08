<?php

return [

    'api_key' => env('GEMINI_API_KEY'),

    // Use Gemini 2.5 Flash (New Stable Release)
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    // IMPORTANT: Use v1 (not v1beta)
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1'),

    'timeout' => env('GEMINI_TIMEOUT', 60),

    'max_retries' => env('GEMINI_MAX_RETRIES', 3),

    'temperature' => env('GEMINI_TEMPERATURE', 0.7),

    'max_tokens' => env('GEMINI_MAX_TOKENS', 8192),
];

