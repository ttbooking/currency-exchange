<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Contracts\CurrencyPair as CurrencyPairContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate as ExchangeRateContract;

final class ExchangeRate implements ExchangeRateContract
{
    public function __construct(
        private CurrencyPairContract $currencyPair,
        private float $value,
        private \DateTimeInterface $date,
        private ?string $serviceName = null,
    ) {
    }

    public function getCurrencyPair(): CurrencyPairContract
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

    public function getServiceName(): ?string
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

    public function jsonSerialize(): array
    {
        return [
            'currency_pair' => $this->currencyPair,
            'date' => $this->date->format('Y-m-d'),
            'service' => $this->serviceName,
            'rate' => $this->value,
        ];
    }

    public static function fromArray(array $exchangeRate): self
    {
        return new self(
            CurrencyPair::fromArray($exchangeRate['currency_pair']),
            $exchangeRate['rate'],
            new \DateTimeImmutable($exchangeRate['date']),
            $exchangeRate['service']
        );
    }
}
