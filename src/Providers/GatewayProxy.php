<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\StringUtil;

class GatewayProxy extends HttpService
{
    protected const URL = 'http://cxgw/api/rate';

    public function has(ExchangeRateQuery $query): bool
    {
        // TODO: Implement has() method.
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $content = $this->request($this->buildUrl($query));
        $result = StringUtil::jsonToArray($content);
    }

    public function getName(): string
    {
        return 'currency_exchange_gateway';
    }

    private function buildUrl(ExchangeRateQuery $query): string
    {
        return $query->isHistorical()

            ? sprintf('%s/%s/%s?date=%s', static::URL,
                $query->getCurrencyPair()->getBaseCurrency(),
                $query->getCurrencyPair()->getQuoteCurrency(),
                $query->getDate()->format('Y-m-d'),
            )

            : sprintf('%s/%s/%s', static::URL,
                $query->getCurrencyPair()->getBaseCurrency(),
                $query->getCurrencyPair()->getQuoteCurrency(),
            );
    }
}
