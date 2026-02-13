<?php

declare(strict_types=1);

return [
    'shopify' => [
        'api_key' => env('SHOPIFY_API_KEY'),
        'api_secret' => env('SHOPIFY_API_SECRET'),
        'store_domain' => env('SHOPIFY_STORE_DOMAIN'),
        'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-04'),
        'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
        'saved_catalog' => env('SHOPIFY_SAVED_CATALOG'),
        'use_mock' => env('SHOPIFY_USE_MOCK', false),
    ],
];
