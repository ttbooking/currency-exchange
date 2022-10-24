<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Contracts\CurrencyPair;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate as ExchangeRateContract;

final class ExchangeRate implements ExchangeRateContract
{
    public function __construct(
        private readonly CurrencyPair $currencyPair,
        private readonly float $value,
        private readonly \DateTimeInterface $date,
        private readonly string $serviceName,
    ) {
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function swapCurrencyPair(): self
    {
        return new self($this->currencyPair->swap(), 1 / $this->value, $this->date, $this->serviceName);
    }

    public function __toString(): string
    {
        return sprintf('%s %.4f', $this->currencyPair, $this->value);
    }
}
