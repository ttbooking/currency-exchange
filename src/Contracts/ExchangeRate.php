<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Contracts;

interface ExchangeRate extends \JsonSerializable, \Stringable
{
    public function getValue(): float;

    public function getFactualDate(): \DateTimeInterface;

    public function getRequestedDate(): \DateTimeInterface;

    public function getCurrencyPair(): CurrencyPair;

    public function isAuthoritative(): bool;

    public function getServiceName(): ?string;

    public function swapCurrencyPair(): self;
}
