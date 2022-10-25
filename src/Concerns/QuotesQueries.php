<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Concerns;

use Illuminate\Support\Facades\Date;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;
use TTBooking\CurrencyExchange\CurrencyPair;
use TTBooking\CurrencyExchange\ExchangeRateQuery;

trait QuotesQueries
{
    protected static function quote(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): ExchangeRateQueryContract
    {
        if ($query instanceof ExchangeRateQueryContract) {
            return $query;
        }

        return new ExchangeRateQuery(CurrencyPair::fromString($query), Date::make($date), $options);
    }
}
