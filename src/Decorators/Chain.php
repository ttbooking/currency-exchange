<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\ChainException;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;

class Chain implements ExchangeRateProvider
{
    /**
     * Creates a new chain provider.
     *
     * @param iterable<ExchangeRateProvider> $providers
     */
    public function __construct(protected iterable $providers = [])
    {
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $exceptions = [];

        foreach ($this->providers as $provider) {
            if (!$provider->has($query)) {
                continue;
            }

            try {
                return $provider->get($query);
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }

        empty($exceptions)
            ? throw new UnsupportedExchangeQueryException
            : throw new ChainException($exceptions);
    }

    public function has(ExchangeRateQuery $query): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($query)) {
                return true;
            }
        }

        return false;
    }
}
