<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
    ],

    'openmeteo' => [
        'base_url' => env('OPENMETEO_BASE_URL', 'https://api.open-meteo.com/v1'),
        'geocoding_url' => env('GEOCODING_BASE_URL', 'https://geocoding-api.open-meteo.com/v1'),
        'weather_cache_ttl' => env('WEATHER_CACHE_TTL', 900), // 15 minutes
        'geocoding_cache_ttl' => env('GEOCODING_CACHE_TTL', 86400), // 24 hours
    ],
];
