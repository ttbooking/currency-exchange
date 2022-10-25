<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Concerns\QuotesQueries;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;

final class ExchangeRateProvider implements ExchangeRateProviderContract
{
    use QuotesQueries;

    public function __construct(private ExchangeRateProviderContract $provider)
    {
    }

    public function has(ExchangeRateQuery|string $query, mixed $date = null, array $options = []): bool
    {
        return $this->provider->has(self::quote($query, $date, $options));
    }

    public function get(ExchangeRateQuery|string $query, mixed $date = null, array $options = []): ExchangeRate
    {
        return $this->provider->get(self::quote($query, $date, $options));
    }
}
