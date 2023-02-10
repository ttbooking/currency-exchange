<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Closure;
use DateInterval;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;
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
        protected ?CacheInterface $cache = null,
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
        if (! $this->has($query)) {
            throw new UnsupportedExchangeQueryException;
        }

        $rates = retry(2, function ($attempt) {
            $content = $this->request($this->buildUrl('v1/public/rates'), [
                'Authorization' => 'Bearer '.$this->getToken((bool) ($attempt - 1)),
                'Accept' => 'application/json',
            ]);

            $result = StringUtil::jsonToArray($content);
            if (! isset($result['Rates'])) {
                throw new \RuntimeException('Service has returned malformed response');
            }

            return $result['Rates'];
        });

        $currencyPair = $query->getCurrencyPair();
        $entryId = array_search($currencyPair->getBaseCurrency(), array_column($rates, 'currency'));

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
        $entry = $rates[$entryId];
        $operation = ($this->config['sell'] ?? true) ? 'sell' : 'purchase';

        if (! isset($entry[$operation])) {
            throw new \RuntimeException('Service has returned malformed response');
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $entry['dateTime'] ?? null);
        if (! $date) {
            throw new UnsupportedExchangeQueryException;
        }

        return $this->createRate($currencyPair, $entry[$operation], $date, $query->getDate());
    }

    private function getToken(bool $fresh = false): string
    {
        $cacheKey = $this->getCacheKey();
        $fresh && $this->cache->delete($cacheKey);

        return $this->remember($cacheKey, function () {
            $clientId = $this->config['client_id'] ?? '';
            $clientSecret = $this->config['client_secret'] ?? '';

            $content = $this->request(
                $this->buildUrl('v1/auth/token'),
                [
                    'Authorization' => 'Basic '.base64_encode("$clientId:$clientSecret"),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
                'POST',
                [
                    'grant_type' => 'client_credentials',
                    'scope' => 'bcc.application.public',
                ]
            );

            $result = StringUtil::jsonToArray($content);

            return [$result['access_token'], $result['expires_in'] ?? 3600];
        });
    }

    private function buildUrl(string $endpoint = ''): string
    {
        return rtrim($this->config['url'] ?? static::URL, '/').($endpoint ? '/'.ltrim($endpoint, '/') : '');
    }

    private function getCacheKey(): string
    {
        $cacheKeyPrefix = $this->config['token_cache_key_prefix'] ?? '';
        $cacheKeyPrefix = preg_replace('#[{}()/\\\@]#', '-', $cacheKeyPrefix);

        $cacheKey = $cacheKeyPrefix.sha1($this->buildUrl().($this->config['client_id'] ?? ''));
        if (strlen($cacheKey) > 64) {
            throw new \Exception("Cache key length exceeds 64 characters ('$cacheKey'). This violates PSR-6 standard");
        }

        return $cacheKey;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template TCacheValue
     *
     * @param  string  $key
     * @param  Closure(): array{0: TCacheValue, 1?: DateInterval|int|null|false}  $callback
     * @return TCacheValue
     */
    private function remember(string $key, Closure $callback): mixed
    {
        if (! $this->cache) {
            return $callback()[0];
        }

        if (! is_null($value = $this->cache->get($key))) {
            return $value;
        }

        [$value, $ttl] = $callback() + [1 => false];
        $ttl !== false && $this->cache->set($key, $value, $ttl);

        return $value;
    }
}
