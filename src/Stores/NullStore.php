<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Stores;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;
use TTBooking\CurrencyExchange\Providers\NullProvider;

class NullStore extends NullProvider implements ExchangeRateStore
{
    public function store(ExchangeRate $exchangeRate): ExchangeRate
    {
        return $exchangeRate;
    }
}
