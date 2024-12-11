<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;

class Reverse implements ExchangeRateProvider
{
    public function __construct(protected ExchangeRateProvider $provider) {}

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        try {
            return $this->provider->get($query);
        } catch (UnsupportedExchangeQueryException $e) {
            try {
                return $this->provider->get($query->swapCurrencyPair())->swapCurrencyPair();
            } catch (UnsupportedExchangeQueryException) {
                throw $e;
            }
        }
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return $this->provider->has($query)
            || $this->provider->has($query->swapCurrencyPair());
    }
}
