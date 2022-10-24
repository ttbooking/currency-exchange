<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use TTBooking\CurrencyExchange\Contracts\CurrencyPair;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;

final class ExchangeRateQuery implements ExchangeRateQueryContract
{
    public function __construct(
        private CurrencyPair $currencyPair,
        private ?\DateTimeInterface $date = null,
        private array $options = [],
    ) {
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function swapCurrencyPair(): self
    {
        return new self($this->currencyPair->swap(), $this->date, $this->options);
    }
}
