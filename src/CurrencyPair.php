<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Contracts\CurrencyPair as CurrencyPairContract;

final class CurrencyPair implements CurrencyPairContract
{
    public function __construct(
        private readonly string $baseCurrency,
        private readonly string $quoteCurrency,
    ) {
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getQuoteCurrency(): string
    {
        return $this->quoteCurrency;
    }

    public function isIdentical(): bool
    {
        return $this->baseCurrency === $this->quoteCurrency;
    }

    public function swap(): self
    {
        return new self($this->quoteCurrency, $this->baseCurrency);
    }

    public function __toString(): string
    {
        return $this->baseCurrency.'/'.$this->quoteCurrency;
    }

    public function jsonSerialize(): array
    {
        return [
            'base' => $this->baseCurrency,
            'quote' => $this->quoteCurrency,
        ];
    }
}
