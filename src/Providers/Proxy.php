<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\StringUtil;

class Proxy extends HttpService
{
    protected const SERVICE_NAME = 'currency_exchange_gateway';

    public function __construct(
        protected array $config = [],
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) {
        parent::__construct($httpClient, $requestFactory);
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return true;
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $content = $this->request($this->buildUrl($query), ['Accept' => 'application/json']);
        $result = StringUtil::jsonToArray($content);

        try {
            return ExchangeRate::fromArray($result);
        } catch (\Throwable) {
            throw new UnsupportedExchangeQueryException;
        }
    }

    private function buildUrl(ExchangeRateQuery $query): string
    {
        return sprintf('%s/%s/%s?date=%s', $this->config['url'],
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            $query->getDate()->format('Y-m-d'),
        );
    }
}
