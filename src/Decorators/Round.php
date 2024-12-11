<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\ExchangeRate;

class Round implements ExchangeRateProvider
{
    public function __construct(protected ExchangeRateProvider $provider, protected int $precision = 8) {}

    public function has(ExchangeRateQuery $query): bool
    {
        return $this->provider->has($query);
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $exchangeRate = $this->provider->get($query);

        return new ExchangeRate(
            $exchangeRate->getCurrencyPair(),
            round($exchangeRate->getValue(), $this->precision, PHP_ROUND_HALF_EVEN),
            $exchangeRate->getFactualDate(),
            $exchangeRate->getRequestedDate(),
            $exchangeRate->getServiceName()
        );
    }
}
