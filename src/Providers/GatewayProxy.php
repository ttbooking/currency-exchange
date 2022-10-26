<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\StringUtil;

class GatewayProxy extends HttpService
{
    public function __construct(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        protected array $config = [],
    ) {
        parent::__construct($httpClient, $requestFactory);
    }

    public function has(ExchangeRateQuery $query): bool
    {
        // TODO: Implement has() method.
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $content = $this->request($this->buildUrl($query), ['Accept' => 'application/json']);
        $result = StringUtil::jsonToArray($content);

        return ExchangeRate::fromArray($result);
    }

    public function getName(): string
    {
        return 'currency_exchange_gateway';
    }

    private function buildUrl(ExchangeRateQuery $query): string
    {
        return $query->isHistorical()

            ? sprintf('%s/%s/%s?date=%s', $this->config['url'],
                $query->getCurrencyPair()->getBaseCurrency(),
                $query->getCurrencyPair()->getQuoteCurrency(),
                $query->getDate()->format('Y-m-d'),
            )

            : sprintf('%s/%s/%s', $this->config['url'],
                $query->getCurrencyPair()->getBaseCurrency(),
                $query->getCurrencyPair()->getQuoteCurrency(),
            );
    }
}
