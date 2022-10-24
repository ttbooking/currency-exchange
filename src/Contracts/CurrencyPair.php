<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface CurrencyPair extends \Stringable
{
    public function getBaseCurrency(): string;

    public function getQuoteCurrency(): string;

    public function isIdentical(): bool;

    public function swap(): self;

    public function __toString(): string;
}
