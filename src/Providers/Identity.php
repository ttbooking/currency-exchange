<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\ExchangeRate;

class Identity implements ExchangeRateProvider
{
    public function __construct(protected ?ExchangeRateProvider $provider = null)
    {
        $this->provider ??= new ExchangeRateNullProvider;
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return $query->getCurrencyPair()->isIdentical()
            || $this->provider->has($query);
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        if ($query->getCurrencyPair()->isIdentical()) {
            return new ExchangeRate($query->getCurrencyPair(), 1, $query->getDate(), $query->getDate());
        }

        return $this->provider->get($query);
    }
}
