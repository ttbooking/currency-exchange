<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\StringUtil;

class NationalBankOfRepublicKazakhstan extends HttpService
{
    protected const URL = 'https://nationalbank.kz/rss/get_rates.cfm';

    public function has(ExchangeRateQuery $query): bool
    {
        return 'KZT' === $query->getCurrencyPair()->getQuoteCurrency();
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        if (!$this->has($query)) {
            throw new UnsupportedExchangeQueryException;
        }

        $currencyPair = $query->getCurrencyPair();
        $baseCurrency = $currencyPair->getBaseCurrency();

        $content = $this->request($this->buildUrl($query->getDate()));
        $element = StringUtil::xmlToElement($content);

        $elements = $element->xpath('./item[title="'.$baseCurrency.'"]');
        $responseDate = \DateTimeImmutable::createFromFormat('!d.m.Y', (string) $element['date']);

        $rate = $elements['0']->description;
        $unit = $elements['0']->quant;

        return $this->createRate($currencyPair, $rate / $unit, $responseDate, $query->getDate());
    }

    /**
     * Builds the url.
     *
     * @param \DateTimeInterface $requestedDate
     *
     * @return string
     */
    private function buildUrl(\DateTimeInterface $requestedDate): string
    {
        return static::URL.'?fdate='.$requestedDate->format('d.m.Y');
    }
}
