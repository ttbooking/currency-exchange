<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

use TTBooking\CurrencyExchange\Exceptions\ExchangeRateStoreException;

interface ExchangeRateStore extends ExchangeRateProvider
{
    /**
     * @param ExchangeRate $exchangeRate
     * @return ExchangeRate
     *
     * @throws ExchangeRateStoreException
     */
    public function store(ExchangeRate $exchangeRate): ExchangeRate;
}
