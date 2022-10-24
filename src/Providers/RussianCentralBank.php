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

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $currencyPair = $query->getCurrencyPair();
        $baseCurrency = $currencyPair->getBaseCurrency();
        $formattedDate = $query->getDate()->format('d.m.Y');

        $content = $this->request($query->isHistorical()
            ? static::URL.'?'.http_build_query(['date_req' => $formattedDate])
            : static::URL
        );
        $element = StringUtil::xmlToElement($content);

        $elements = $element->xpath('./Valute[CharCode="'.$baseCurrency.'"]');
        $responseDate = \DateTimeImmutable::createFromFormat('!d.m.Y', (string) $element['Date']);

        if (empty($elements) || $query->isHistorical() && $formattedDate !== (string) $element['Date']) {
            throw new UnsupportedExchangeQueryException;
        }

        $rate = str_replace(',', '.', (string) $elements['0']->Value);
        $nominal = str_replace(',', '.', (string) $elements['0']->Nominal);

        return $this->createRate($currencyPair, $rate / $nominal, $responseDate);
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return 'RUB' === $query->getCurrencyPair()->getQuoteCurrency();
    }

    public function getName(): string
    {
        return 'russian_central_bank';
    }
}
