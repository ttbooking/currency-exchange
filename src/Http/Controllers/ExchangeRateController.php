<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use TTBooking\CurrencyExchange\CurrencyPair;
use TTBooking\CurrencyExchange\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Facades\ExchangeRate;

class ExchangeRateController
{
    public function getExchangeRate(string $base, string $quote, Request $request): \JsonSerializable
    {
        $date = Date::make($request->query('date'));
        $service = $request->query('service');
        $options = array_filter(compact('service'));

        return ExchangeRate::provider($service)->get(
            new ExchangeRateQuery(
                new CurrencyPair($base, $quote),
                $date, $options,
            )
        );
    }
}
