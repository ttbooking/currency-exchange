<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Facades;

use Illuminate\Support\Facades\Facade;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate as ExchangeRateContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\ExchangeRateProvider;

/**
 * @method static ExchangeRateProvider provider(string $provider = null)
 * @method static bool has(ExchangeRateQuery|string $query, mixed $date = null, array $options = [])
 * @method static ExchangeRateContract get(ExchangeRateQuery|string $query, mixed $date = null, array $options = [])
 *
 * @see \TTBooking\CurrencyExchange\ExchangeRateManager
 */
class ExchangeRate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'currency-exchange';
    }
}
