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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'shipping_allowed_countries' => array_values(array_filter(array_map('trim', explode(',', (string) env('STRIPE_SHIPPING_ALLOWED_COUNTRIES', 'US,CA'))))),
        'shipping_rate_standard_id' => env('STRIPE_SHIPPING_RATE_STANDARD_ID'),
        'shipping_standard_amount' => (int) env('STRIPE_SHIPPING_STANDARD_AMOUNT', 999),
        'shipping_tier2_amount' => (int) env('STRIPE_SHIPPING_TIER2_AMOUNT', 1299),
        'shipping_tier3_amount' => (int) env('STRIPE_SHIPPING_TIER3_AMOUNT', 1599),
        'shipping_tier2_min_qty' => (int) env('STRIPE_SHIPPING_TIER2_MIN_QTY', 25),
        'shipping_tier3_min_qty' => (int) env('STRIPE_SHIPPING_TIER3_MIN_QTY', 50),
        'shipping_currency' => env('STRIPE_SHIPPING_CURRENCY', 'usd'),
        'net30_due_days' => (int) env('STRIPE_NET30_DUE_DAYS', 30),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
