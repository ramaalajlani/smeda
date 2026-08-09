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

    /*
    | ميكروسيرفس تصنيف الاحتياج (زر AI العائم)
    | مثال: AI_CLASSIFY_URL=http://127.0.0.1:9000/classify
    */
    'ai' => [
        'classify_url' => env('AI_CLASSIFY_URL'),
        'classify_token' => env('AI_CLASSIFY_TOKEN'),
        'classify_method' => env('AI_CLASSIFY_METHOD', 'POST'),
        'classify_timeout' => (int) env('AI_CLASSIFY_TIMEOUT', 20),
    ],

    /*
    | خدمة «المستشار الذكي» (محادثة AI) — يتصل بها الباك إند فقط، لا المتصفح.
    | مثال: AI_SERVICE_URL=https://ai.smedc-sy.tech
    |
    | استثناء وحيد: الصوت والمكالمة يعملان على WebSocket لا يمكن لـ PHP تمريره،
    | فيتصل بهما المتصفح مباشرة على ws_url. يُشتق من url تلقائياً إن تُرك فارغاً،
    | ويُسلَّم للواجهة عبر /ai/config فلا يُكتب في جافاسكربت.
    */
    'ai_service' => [
        'url' => env('AI_SERVICE_URL', 'https://ai.smedc-sy.tech'),
        'ws_url' => env('AI_SERVICE_WS_URL'),
        'api_key' => env('AI_SERVICE_KEY'),
        'department_id' => env('AI_SERVICE_DEPARTMENT_ID', 'advisor'),
        'voice_department' => env('AI_SERVICE_VOICE_DEPARTMENT', 'advisor'),
        'timeout' => (int) env('AI_SERVICE_TIMEOUT', 60),
        'session_ttl' => (int) env('AI_SERVICE_SESSION_TTL', 720),
        'history_ttl' => (int) env('AI_SERVICE_HISTORY_TTL', 43200),
        'history_max' => (int) env('AI_SERVICE_HISTORY_MAX', 40),
        'config_ttl' => (int) env('AI_SERVICE_CONFIG_TTL', 10),
    ],

];
