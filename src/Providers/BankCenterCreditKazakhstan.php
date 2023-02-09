<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\StringUtil;

class BankCenterCreditKazakhstan extends HttpService
{
    protected const URL = 'https://api.bcc.kz/bcc/production';

    public function __construct(
        protected array $config = [],
        ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        protected ?StreamFactoryInterface $streamFactory = null,
    ) {
        parent::__construct($httpClient, $requestFactory);
        $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return 'KZT' === $query->getCurrencyPair()->getQuoteCurrency()
            && in_array($query->getCurrencyPair()->getBaseCurrency(), ['RUB', 'KGS', 'USD', 'EUR', 'GBP'])
            && $query->getDate()->format('Y-m-d') === (new \DateTimeImmutable)->format('Y-m-d');
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $currencyPair = $query->getCurrencyPair();

        if (!$this->has($query)) {
            throw new UnsupportedExchangeQueryException;
        }

        $content = $this->request(static::URL.'/v1/public/rates', [
            'Authorization' => 'Bearer '.$this->getToken(),
            'Accept' => 'application/json',
        ]);
        $result = StringUtil::jsonToArray($content)['Rates'];
        $entryId = array_search($currencyPair->getBaseCurrency(), array_column($result, 'currency'));

        if ($entryId === false) {
            throw new UnsupportedExchangeQueryException;
        }

        /**
         * @var array{
         *     currency: string,
         *     purchase: float,
         *     sell: float,
         *     purchaseDelta: int,
         *     sellDelta: int,
         *     dateTime: string
         * } $entry
         */
        $entry = $result[$entryId];
        $operation = ($this->config['sell'] ?? true) ? 'sell' : 'purchase';

        if (!isset($entry[$operation])) {
            throw new \RuntimeException('Service has returned malformed response');
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $entry['dateTime'] ?? null);
        if (!$date) {
            throw new UnsupportedExchangeQueryException;
        }

        return $this->createRate($currencyPair, $entry[$operation], $date, $query->getDate());
    }

    private function getToken(): string
    {
        $clientId = $this->config['client_id'] ?? '';
        $clientSecret = $this->config['client_secret'] ?? '';

        $content = $this->request(
            static::URL.'/v1/auth/token',
            [
                'Authorization' => 'Basic '.base64_encode("$clientId:$clientSecret"),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'POST',
            [
                'grant_type'=> 'client_credentials',
                'scope' => 'bcc.application.public',
            ]
        );

        return StringUtil::jsonToArray($content)['access_token'];
    }
}
