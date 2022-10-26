<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;

class ReversibleExchangeRateStore extends ReversibleExchangeRateProvider implements ExchangeRateStore
{
    public function store(ExchangeRate $exchangeRate): ExchangeRate
    {
        return $this->provider->store($exchangeRate);
    }
}
