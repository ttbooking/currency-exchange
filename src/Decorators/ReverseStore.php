<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;

class ReverseStore extends Reverse implements ExchangeRateStore
{
    public function store(ExchangeRate $exchangeRate): ExchangeRate
    {
        return $this->provider->store($exchangeRate);
    }
}
