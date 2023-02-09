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
                TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus::class,
                TTBooking\CurrencyExchange\Providers\BankCenterCreditKazakhstan::class,
                TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicKazakhstan::class,
                TTBooking\CurrencyExchange\Providers\CentralBankOfRepublicUzbekistan::class,
                TTBooking\CurrencyExchange\Providers\RussianCentralBank::class,
            ],
            'cross_currency' => env('CX_CHAIN_CROSS', env('CX_CROSS', 'RUB')),
        ],

        'currency_exchange_gateway' => [
            'url' => env('CX_PROXY_URL'),
            'cross_currency' => env('CX_PROXY_CROSS', env('CX_CROSS', 'RUB')),
        ],

        'bank_center_credit_kazakhstan' => [
            'client_id' => env('CX_BCC_ID'),
            'client_secret' => env('CX_BCC_SECRET'),
            'sell' => env('CX_BCC_SELL', true),
        ],

    ],

    'stores' => [

        'cache' => [
            'key_prefix' => env('CX_CACHE_PREFIX', 'exchange_rates:'),
            'ttl' => env('CX_CACHE_TTL', 86400),
        ],

        'database' => [
            'table' => env('CX_TABLE', 'exchange_rates'),
        ],

    ],

];
