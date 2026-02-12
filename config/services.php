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

    'ai_blog' => [
        'text_endpoint' => env('AI_TEXT_ENDPOINT'),
        'text_key' => env('AI_TEXT_KEY'),
        'text_model' => env('AI_TEXT_MODEL', 'gpt-4o-mini'),
        'chat_model' => env('AI_CHAT_MODEL', env('AI_TEXT_MODEL', 'gpt-4o-mini')),
        'image_endpoint' => env('IMAGE_AI_ENDPOINT', 'https://image-ai.desmart79.workers.dev'),
        'image_key' => env('IMAGE_AI_KEY'),
        'trends_endpoint' => env('AI_TRENDS_ENDPOINT', 'https://serpapi.com/search.json'),
        'trends_key' => env('AI_TRENDS_KEY', env('SERPAPI_KEY')),
    ],

];
