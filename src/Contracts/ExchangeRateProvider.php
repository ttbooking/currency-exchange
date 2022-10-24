<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;

interface ExchangeRateProvider
{
    /**
     * @param ExchangeRateQuery $query
     * @return bool
     */
    public function has(ExchangeRateQuery $query): bool;

    /**
     * @param ExchangeRateQuery $query
     * @return ExchangeRate
     *
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQuery $query): ExchangeRate;
}
