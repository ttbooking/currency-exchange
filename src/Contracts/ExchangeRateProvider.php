<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;

interface ExchangeRateProvider
{
    public function has(ExchangeRateQuery $query): bool;

    /**
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQuery $query): ExchangeRate;
}
