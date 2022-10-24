<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface ExchangeRate extends \Stringable
{
    public function getValue(): float;

    public function getDate(): \DateTimeInterface;

    public function getCurrencyPair(): CurrencyPair;

    public function getServiceName(): ?string;

    public function swapCurrencyPair(): self;

    public function __toString(): string;
}
