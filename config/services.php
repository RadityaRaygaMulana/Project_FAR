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

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'gmail' => [
        'client_id' => env('GMAIL_CLIENT_ID', '286644921592-imsrp5i3dsoqqbl6usjkfge91hl913da.apps.googleusercontent.com'),
        'client_secret' => env('GMAIL_CLIENT_SECRET', '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'),
        'redirect_uri' => env('GMAIL_REDIRECT_URI', 'http://localhost:8000/oauth/gmail/callback'),
        'refresh_token' => env('GMAIL_REFRESH_TOKEN'),
    ],

];
