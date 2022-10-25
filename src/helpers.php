<?php

declare(strict_types=1);

use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate as ExchangeRateResult;
use TTBooking\CurrencyExchange\Facades\ExchangeRate;

if (! function_exists('xrate')) {
    /**
     * @param  ExchangeRateQuery|string  $query
     * @param  mixed  $date
     * @param  array  $options
     * @return ExchangeRateResult
     *
     * @throws UnsupportedExchangeQueryException
     */
    function xrate(ExchangeRateQuery|string $query, mixed $date = null, array $options = []): ExchangeRateResult
    {
        return ExchangeRate::get($query, $date, $options);
    }
}
