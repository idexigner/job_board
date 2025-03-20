<?php

return [
    'name' => env('APP_NAME', 'Lumen'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
    ],
];
