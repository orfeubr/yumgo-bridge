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

    'asaas' => [
        'url' => env('ASAAS_URL', 'https://sandbox.asaas.com/api/v3'),
        'api_key' => env('ASAAS_API_KEY'),
        'platform_wallet_id' => env('ASAAS_PLATFORM_WALLET_ID'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
    ],

    'pagarme' => [
        'url' => env('PAGARME_URL', 'https://api.pagar.me/core/v5'),
        'api_key' => env('PAGARME_API_KEY'),
        'encryption_key' => env('PAGARME_ENCRYPTION_KEY'),
        'platform_recipient_id' => env('PAGARME_PLATFORM_RECIPIENT_ID'), // ID do recebedor da plataforma
        'webhook_token' => env('PAGARME_WEBHOOK_TOKEN'),
    ],

    'tributaai' => [
        'platform_token' => env('TRIBUTAAI_PLATFORM_TOKEN'),
    ],

    // OAuth Social Login
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/google/callback',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/facebook/callback',
    ],

];
