<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'ocr' => [
        'url' => env('OCR_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => (int) env('OCR_TIMEOUT_SECONDS', 60),
    ],

    'mom_ai' => [
        'url' => env('MOM_SERVICE_URL', env('OCR_SERVICE_URL', 'http://127.0.0.1:8001')),
        'timeout' => (int) env('MOM_TIMEOUT_SECONDS', 120),
    ],

    'power_automate' => [
        'driver'    => env('MOM_AI_DRIVER', 'auto'),
        'text_url'  => env('POWER_AUTOMATE_TEXT_URL'),
        'audio_url' => env('POWER_AUTOMATE_AUDIO_URL'),
        'api_key'   => env('POWER_AUTOMATE_API_KEY'),
    ],

];
