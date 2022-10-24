<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;

class ExchangeRateCachingDecorator implements ExchangeRateProvider
{
    public function __construct(
        protected ExchangeRateProvider $provider,
        protected ExchangeRateStore $store = new ExchangeRateNullStore,
    ) {
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

        return $this->store->store(
            $this->provider->get($query)
        );
    }
}
