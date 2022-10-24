<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;

class ExchangeRateNullStore extends ExchangeRateNullProvider implements ExchangeRateStore
{
    public function store(ExchangeRate $exchangeRate): ExchangeRate
    {
        return $exchangeRate;
    }
}
