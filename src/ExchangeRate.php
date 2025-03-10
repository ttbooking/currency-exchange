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
        private \DateTimeInterface $factualDate,
        private \DateTimeInterface $requestedDate,
        private ?string $serviceName = null,
    ) {}

    public function getCurrencyPair(): CurrencyPairContract
    {
        return $this->currencyPair;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getFactualDate(): \DateTimeInterface
    {
        return $this->factualDate;
    }

    public function getRequestedDate(): \DateTimeInterface
    {
        return $this->requestedDate;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function isAuthoritative(): bool
    {
        return ! ($this->requestedDate > $this->factualDate && $this->requestedDate > new \DateTimeImmutable);
    }

    public function swapCurrencyPair(): self
    {
        return new self(
            $this->currencyPair->swap(),
            1 / $this->value,
            $this->factualDate,
            $this->requestedDate,
            $this->serviceName
        );
    }

    public function __toString(): string
    {
        return sprintf('%s %.4f', $this->currencyPair, $this->value);
    }

    public function jsonSerialize(): array
    {
        return [
            'currency_pair' => (string) $this->currencyPair,
            'factual_date' => $this->factualDate->format('Y-m-d'),
            'requested_date' => $this->requestedDate->format('Y-m-d'),
            'service' => $this->serviceName,
            'rate' => $this->value,
        ];
    }

    public static function fromArray(array $exchangeRate): self
    {
        return new self(
            CurrencyPair::parse($exchangeRate['currency_pair']),
            $exchangeRate['rate'],
            new \DateTimeImmutable($exchangeRate['factual_date']),
            new \DateTimeImmutable($exchangeRate['requested_date']),
            $exchangeRate['service']
        );
    }
}
