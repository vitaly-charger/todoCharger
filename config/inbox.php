<?php

return [
    /*
     * Only this Google account is allowed to use the app.
     */
    'allowed_email' => env('ALLOWED_EMAIL', 'vitaly.vinnikov@gmail.com'),

    /*
     * AI provider settings.
     */
    'ai' => [
        'provider' => env('AI_PROVIDER', 'claude'), // claude|openai
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],
        'min_confidence' => (float) env('AI_MIN_CONFIDENCE', 0.6),
        'store_raw_body' => (bool) env('AI_STORE_RAW_BODY', true),
    ],

    /*
     * Provider tokens / credentials.
     */
    'providers' => [
        'gmail' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        ],
        'slack' => [
            'client_id' => env('SLACK_CLIENT_ID'),
            'client_secret' => env('SLACK_CLIENT_SECRET'),
        ],
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        ],
        'monday' => [
            'api_token' => env('MONDAY_API_TOKEN'),
        ],
        'wrike' => [
            'client_id' => env('WRIKE_CLIENT_ID'),
            'client_secret' => env('WRIKE_CLIENT_SECRET'),
            'api_token' => env('WRIKE_API_TOKEN'),
        ],
    ],

    'sync' => [
        'gmail_minutes' => (int) env('SYNC_GMAIL_MINUTES', 5),
        'slack_minutes' => (int) env('SYNC_SLACK_MINUTES', 5),
        'monday_minutes' => (int) env('SYNC_MONDAY_MINUTES', 5),
        'wrike_minutes' => (int) env('SYNC_WRIKE_MINUTES', 5),
        'telegram_fallback_minutes' => (int) env('SYNC_TELEGRAM_FALLBACK_MINUTES', 5),
    ],
];
