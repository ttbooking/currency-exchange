<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Exchange Rate Provider
    |--------------------------------------------------------------------------
    */

    'provider' => env('CX_PROVIDER', 'chain'),

    'providers' => [

        'gateway_proxy' => [
            'url' => env('CX_PROXY_URL'),
        ],

    ],

];
