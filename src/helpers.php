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

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @template TReturn
     *
     * @param  int|array  $times
     * @param  callable(int=): TReturn  $callback
     * @param  int|Closure  $sleepMilliseconds
     * @param  (callable(Exception): bool)|null  $when
     * @return TReturn
     *
     * @throws Exception
     */
    function retry(
        int|array $times,
        callable $callback,
        int|Closure $sleepMilliseconds = 0,
        callable $when = null
    ): mixed {
        $attempts = 0;

        $backoff = [];

        if (is_array($times)) {
            $backoff = $times;

            $times = count($times) + 1;
        }

        beginning:
        $attempts++;
        $times--;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if ($times < 1 || ($when && ! $when($e))) {
                throw $e;
            }

            $sleepMilliseconds = $backoff[$attempts - 1] ?? $sleepMilliseconds;

            if ($sleepMilliseconds) {
                usleep(value($sleepMilliseconds, $attempts, $e) * 1000);
            }

            goto beginning;
        }
    }
}
