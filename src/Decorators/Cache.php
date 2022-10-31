<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;
use TTBooking\CurrencyExchange\Stores\NullStore;

class Cache implements ExchangeRateProvider
{
    public function __construct(
        protected ExchangeRateProvider $provider,
        protected ?ExchangeRateStore $store = null,
    ) {
        $this->store ??= new NullStore;
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return $this->store->has($query)
            || $this->provider->has($query);
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        if ($this->store->has($query)) {
            return $this->store->get($query);
        }

        $exchangeRate = $this->provider->get($query);

        if ($exchangeRate->isAuthoritative()) {
            $this->store->store($exchangeRate);
        }

        return $exchangeRate;
    }
}
