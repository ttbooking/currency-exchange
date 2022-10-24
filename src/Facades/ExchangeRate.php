<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate as ExchangeRateContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;

/**
 * @method static bool has(ExchangeRateQuery $query)
 * @method static ExchangeRateContract get(ExchangeRateQuery $query)
 *
 * @see \TTBooking\CurrencyExchange\Providers\Chain
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
        return ExchangeRateProvider::class;
    }
}
