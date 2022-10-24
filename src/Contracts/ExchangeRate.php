<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface ExchangeRate extends \Stringable, \JsonSerializable
{
    public function getValue(): float;

    public function getDate(): \DateTimeInterface;

    public function getCurrencyPair(): CurrencyPair;

    public function getServiceName(): ?string;

    public function swapCurrencyPair(): self;
}
