<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Exchange Rate Provider
    |--------------------------------------------------------------------------
    */

    'provider' => env('CX_PROVIDER', 'chain'),

    'providers' => [

        'chain' => [
            'services' => [
                'national_bank_of_republic_belarus',
                'central_bank_of_republic_uzbekistan',
                'russian_central_bank',
            ],
        ],

        'cache' => [
            'ttl' => env('CX_CACHE_TTL', 3600),
        ],

        'database' => [
            'table' => env('CX_TABLE', 'exchange_rates'),
        ],

        'gateway_proxy' => [
            'url' => env('CX_PROXY_URL'),
        ],

    ],

];
