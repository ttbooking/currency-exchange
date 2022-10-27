<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Psr\SimpleCache\CacheInterface;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;

class ExchangeRateCacheStore implements ExchangeRateStore
{
    public function __construct(
        protected CacheInterface $cache,
        protected array $options = [],
    ) {
    }

    public function has(ExchangeRateQuery $query): bool
    {
        return $this->cache->has($this->getCacheKey($query));
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        if (null === $exchangeRate = $this->cache->get($this->getCacheKey($query))) {
            throw new UnsupportedExchangeQueryException;
        }

        return $exchangeRate;
    }

    public function store(ExchangeRate $exchangeRate): ExchangeRate
    {
        $this->cache->set($this->getCacheKey($exchangeRate), $exchangeRate, $this->getCacheTTL());

        return $exchangeRate;
    }

    private function getCacheKey(ExchangeRateQuery|ExchangeRate $query): string
    {
        $cacheKeyPrefix = $this->options['cache_key_prefix'] ?? '';
        $cacheKeyPrefix = preg_replace('#[{}()/\\\@]#', '-', $cacheKeyPrefix);

        $cacheKey = $cacheKeyPrefix.sha1($query->getCurrencyPair().$query->getDate()->format('Y-m-d'));
        if (strlen($cacheKey) > 64) {
            throw new \Exception("Cache key length exceeds 64 characters ('$cacheKey'). This violates PSR-6 standard");
        }

        return $cacheKey;
    }

    private function getCacheTTL(): ?int
    {
        return $this->options['cache_ttl'] ?? null;
    }
}
