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
                'national_bank_of_republic_kazakhstan',
                'central_bank_of_republic_uzbekistan',
                'russian_central_bank',
            ],
        ],

        'bank_center_credit_kazakhstan' => [
            'client_id' => env('CX_BCC_ID'),
            'client_secret' => env('CX_BCC_SECRET'),
            'sell' => env('CX_BCC_SELL', true),
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
