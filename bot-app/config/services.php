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

    'anilist' => [
        'url' => env('ANILIST_API_URL', 'https://graphql.anilist.co'),
    ],

    'tmdb' => [
        'url' => env('TMDB_API_URL', 'https://api.themoviedb.org/3'),
        'api_key' => env('TMDB_API_KEY'),
        'image_base_url' => env('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p/original'),
    ],

    'igdb' => [
        'url' => env('IGDB_API_URL', 'https://api.igdb.com/v4'),
        'client_id' => env('IGDB_CLIENT_ID'),
        'access_token' => env('IGDB_ACCESS_TOKEN'),
        'image_base_url' => env('IGDB_IMAGE_BASE_URL', 'https://images.igdb.com/igdb/image/upload/t_1080p'),
    ],

    'superhero' => [
        'url' => env('SUPERHERO_API_URL', 'https://superheroapi.com/api'),
        'token' => env('SUPERHERO_API_TOKEN'),
    ],

];
