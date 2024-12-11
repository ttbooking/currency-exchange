<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Contracts\CurrencyPair as CurrencyPairContract;

final class CurrencyPair implements CurrencyPairContract
{
    public function __construct(
        private string $baseCurrency,
        private string $quoteCurrency,
    ) {}

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

    public static function fromString(string $currencyPair): self
    {
        if (! preg_match('#^[A-Z]{3}/[A-Z]{3}$#', $currencyPair)) {
            throw new \InvalidArgumentException('The currency pair must be in the form "EUR/USD".');
        }

        $parts = explode('/', $currencyPair);

        return new self($parts[0], $parts[1]);
    }

    public static function fromArray(array $currencyPair): self
    {
        return new self($currencyPair['base'], $currencyPair['quote']);
    }

    public static function parse(string|array $currencyPair): self
    {
        return is_string($currencyPair)
            ? self::fromString($currencyPair)
            : self::fromArray($currencyPair);
    }
}
