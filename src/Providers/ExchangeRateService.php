<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Illuminate\Support\Str;
use TTBooking\CurrencyExchange\Contracts\CurrencyPair;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateService as ExchangeRateServiceContract;
use TTBooking\CurrencyExchange\ExchangeRate;

abstract class ExchangeRateService implements ExchangeRateServiceContract
{
    public static function getName(): string
    {
        return defined(static::class.'::SERVICE_NAME')
            ? static::SERVICE_NAME
            : Str::snake(class_basename(static::class));
    }

    /**
     * Creates an exchange rate.
     */
    protected function createRate(
        CurrencyPair $currencyPair,
        float $rate,
        \DateTimeInterface $factualDate,
        \DateTimeInterface $requestedDate = null
    ): ExchangeRate {
        return new ExchangeRate($currencyPair, $rate, $factualDate, $requestedDate ?? $factualDate, static::getName());
    }
}
