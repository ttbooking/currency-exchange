<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface ExchangeRateService extends ExchangeRateProvider
{
    public static function getName(): string;
}
