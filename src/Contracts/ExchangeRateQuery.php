<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface ExchangeRateQuery
{
    public function getCurrencyPair(): CurrencyPair;

    public function getDate(): \DateTimeInterface;

    public function getOption(string $name, mixed $default = null): mixed;

    public function swapCurrencyPair(): self;
}
