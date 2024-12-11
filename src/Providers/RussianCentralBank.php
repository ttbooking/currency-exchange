<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\StringUtil;

class RussianCentralBank extends HttpService
{
    protected const URL = 'https://www.cbr.ru/scripts/XML_daily.asp';

    public function has(ExchangeRateQuery $query): bool
    {
        return $query->getCurrencyPair()->getQuoteCurrency() === 'RUB';
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        if (! $this->has($query)) {
            throw new UnsupportedExchangeQueryException;
        }

        $currencyPair = $query->getCurrencyPair();
        $baseCurrency = $currencyPair->getBaseCurrency();

        $content = $this->request($this->buildUrl($query->getDate()));
        $element = StringUtil::xmlToElement($content);

        $elements = $element->xpath('./Valute[CharCode="'.$baseCurrency.'"]');
        $responseDate = \DateTimeImmutable::createFromFormat('!d.m.Y', (string) $element['Date']);

        if (empty($elements)) {
            throw new UnsupportedExchangeQueryException;
        }

        $rate = str_replace(',', '.', (string) $elements['0']->Value);
        $unit = str_replace(',', '.', (string) $elements['0']->Nominal);

        return $this->createRate($currencyPair, $rate / $unit, $responseDate, $query->getDate());
    }

    /**
     * Builds the url.
     */
    private function buildUrl(\DateTimeInterface $requestedDate): string
    {
        return static::URL.'?'.http_build_query(['date_req' => $requestedDate->format('d.m.Y')]);
    }
}
