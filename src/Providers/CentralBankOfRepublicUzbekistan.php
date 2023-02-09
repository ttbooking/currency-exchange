<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\StringUtil;

class CentralBankOfRepublicUzbekistan extends HttpService
{
    protected const URL = 'https://cbu.uz/common/json/';

    public function has(ExchangeRateQuery $query): bool
    {
        return 'UZS' === $query->getCurrencyPair()->getQuoteCurrency();
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $currencyPair = $query->getCurrencyPair();

        $content = $this->request($this->buildUrl($query->getDate()));
        $element = StringUtil::jsonToArray($content);

        $currencyInfo = array_values(array_filter($element, function ($currency) use ($currencyPair) {
            return $currency['Ccy'] === $currencyPair->getBaseCurrency();
        }));
        if (!empty($currencyInfo)) {
            $rate = (float) $currencyInfo[0]['Rate'];
            $unit = (int) $currencyInfo[0]['Nominal'];

            $date = new \DateTimeImmutable((string) $currencyInfo[0]['Date']);

            return $this->createRate($currencyPair, $rate / $unit, $date, $query->getDate());
        }

        throw new UnsupportedExchangeQueryException;
    }

    public function getName(): string
    {
        return 'central_bank_of_republic_uzbekistan';
    }

    /**
     * Builds the url.
     *
     * @param \DateTimeInterface|null $requestedDate
     *
     * @return string
     */
    private function buildUrl(\DateTimeInterface $requestedDate = null): string
    {
        $date = is_null($requestedDate) ? '' : '?date='.$requestedDate->format('d.m.Y');

        return static::URL.$date;
    }
}
