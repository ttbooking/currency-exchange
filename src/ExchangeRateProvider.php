<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Support\Facades\Date;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;

final class ExchangeRateProvider implements ExchangeRateProviderContract
{
    public function __construct(private ExchangeRateProviderContract $provider) {}

    public function has(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): bool
    {
        return $this->provider->has(self::quote($query, $date, $options));
    }

    public function get(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): ExchangeRate
    {
        return $this->provider->get(self::quote($query, $date, $options));
    }

    protected static function quote(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): ExchangeRateQueryContract
    {
        if ($query instanceof ExchangeRateQueryContract) {
            return $query;
        }

        return new ExchangeRateQuery(CurrencyPair::fromString($query), Date::make($date), $options);
    }
}
