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

    'cerebras' => [
        'key' => env('CEREBRAS_API_KEY'),
        'model' => env('CEREBRAS_MODEL', 'qwen-3-235b-a22b-instruct-2507'),
    ],

    'zydit' => [
        'key' => env('ZYDIT_API_KEY'),
        'model' => env('ZYDIT_MODEL', 'z-ai/glm5'),
        'endpoint' => env('ZYDIT_ENDPOINT', 'https://api.zydit.in/v1/chat/completions'),
    ],

    'llm' => [
        'provider' => env('LLM_PROVIDER', 'cerebras'),
    ],

    'academic_guide' => [
        'endpoint' => env('ACADEMIC_GUIDE_ENDPOINT', 'http://localhost:8000/v1/chat'),
        'top_k' => (int) env('ACADEMIC_GUIDE_TOP_K', 5),
        'timeout' => (int) env('ACADEMIC_GUIDE_TIMEOUT', 3000), # in milliseconds
    ],

];
