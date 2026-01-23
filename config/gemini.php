<?php

return [
    // API Configuration
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-pro'),
    'temperature' => env('GEMINI_TEMPERATURE', 0.1), // Low temperature for consistent grading
    'max_tokens' => env('GEMINI_MAX_TOKENS', 2000),
    'timeout' => env('GEMINI_TIMEOUT', 30),

    // AI Generation Settings
    'generation' => [
        'enabled' => env('AI_GENERATION_ENABLED', true),
        'quality_threshold' => env('AI_QUALITY_THRESHOLD', 0.8),
        'rate_limit_per_hour' => env('AI_RATE_LIMIT_PER_HOUR', 100),
        'max_questions_per_request' => env('AI_MAX_QUESTIONS_PER_REQUEST', 10),
        'cache_ttl' => env('AI_CACHE_TTL', 3600), // 1 hour
    ],

    // Question Generation Prompts
    'prompts' => [
        'mcq' => [
            'system' => 'You are an expert educator creating high-quality multiple choice questions.',
            'template' => 'Create a multiple choice question about {topic} with {difficulty} difficulty. Include 4 options (A, B, C, D) with one correct answer. Provide detailed explanation.',
        ],
        'fill_blank' => [
            'system' => 'You are an expert educator creating fill-in-the-blank questions.',
            'template' => 'Create a fill-in-the-blank question about {topic} with {difficulty} difficulty. Use ______ for blanks. Include expected answer and evaluation hints.',
        ],
        'code' => [
            'system' => 'You are an expert programmer creating coding challenges.',
            'template' => 'Create a {language} coding question about {topic} with {difficulty} difficulty. Include question text, expected code solution, test cases, and evaluation criteria.',
        ],
    ],

    // Quality Validation Rules
    'validation' => [
        'min_question_length' => 10,
        'max_question_length' => 500,
        'min_explanation_length' => 20,
        'max_explanation_length' => 1000,
        'require_unique_options' => true,
        'check_encoding' => true,
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMITING_ENABLED', true),
        'max_attempts_per_minute' => env('AI_MAX_ATTEMPTS_PER_MINUTE', 10),
        'max_attempts_per_hour' => env('AI_MAX_ATTEMPTS_PER_HOUR', 100),
        'block_duration_minutes' => env('AI_BLOCK_DURATION_MINUTES', 15),
    ],

    // Caching
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'prefix' => 'ai_generation:',
        'ttl' => env('AI_CACHE_TTL', 3600),
    ],

    // Monitoring & Analytics
    'monitoring' => [
        'log_requests' => env('AI_LOG_REQUESTS', true),
        'log_errors' => env('AI_LOG_ERRORS', true),
        'track_metrics' => env('AI_TRACK_METRICS', true),
    ],
];