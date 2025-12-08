<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Desk365 API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for Desk365 API integration.
    |
    */

    'base_url' => env('DESK365_BASE_URL', 'https://api.desk365.com'),
    'api_key' => env('DESK365_API_KEY', ''),
    'api_secret' => env('DESK365_API_SECRET', null),
    'timeout' => env('DESK365_TIMEOUT', 30),
    'retry_attempts' => env('DESK365_RETRY_ATTEMPTS', 3),
    'version' => env('DESK365_API_VERSION', 'v3'),
];



