<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Decorators;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;
use TTBooking\CurrencyExchange\CurrencyPair;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\ExchangeRateQuery;

class Cross implements ExchangeRateProvider
{
    public function __construct(protected ExchangeRateProvider $provider, protected string $crossCurrency) {}

    public function get(ExchangeRateQueryContract $query): ExchangeRate
    {
        if ($this->crossCurrency === $query->getCurrencyPair()->getQuoteCurrency() || $this->provider->has($query)) {
            return $this->provider->get($query);
        }

        [$query1, $query2] = $this->fork($query);

        $rate1 = $this->get($query1);
        $rate2 = $this->get($query2);

        return new ExchangeRate(
            $query->getCurrencyPair(),
            $rate1->getValue() / $rate2->getValue(),
            $rate1->getFactualDate(),
            $rate1->getRequestedDate(),
            $rate1->getServiceName(),
        );
    }

    public function has(ExchangeRateQueryContract $query): bool
    {
        if ($this->crossCurrency === $query->getCurrencyPair()->getQuoteCurrency()) {
            return false;
        }

        if ($this->provider->has($query)) {
            return true;
        }

        [$query1, $query2] = $this->fork($query);

        return $this->provider->has($query1) && $this->provider->has($query2);
    }

    /**
     * @return ExchangeRateQuery[]
     */
    protected function fork(ExchangeRateQueryContract $query): array
    {
        $baseCurrency = $query->getCurrencyPair()->getBaseCurrency();
        $quoteCurrency = $query->getCurrencyPair()->getQuoteCurrency();

        $currencyPair1 = new CurrencyPair($baseCurrency, $this->crossCurrency);
        $currencyPair2 = new CurrencyPair($quoteCurrency, $this->crossCurrency);

        return [
            new ExchangeRateQuery($currencyPair1, $query->getDate()),
            new ExchangeRateQuery($currencyPair2, $query->getDate()),
        ];
    }
}
