<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Support\Manager;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;
use TTBooking\CurrencyExchange\Decorators\Cache;
use TTBooking\CurrencyExchange\Decorators\Chain;
use TTBooking\CurrencyExchange\Decorators\Cross;
use TTBooking\CurrencyExchange\Decorators\Identity;
use TTBooking\CurrencyExchange\Decorators\Reverse;
use TTBooking\CurrencyExchange\Decorators\ReverseStore;
use TTBooking\CurrencyExchange\Decorators\Round;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\Providers\CentralBankOfRepublicUzbekistan;
use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus;
use TTBooking\CurrencyExchange\Providers\Proxy;
use TTBooking\CurrencyExchange\Providers\RussianCentralBank;
use TTBooking\CurrencyExchange\Stores\CacheStore;
use TTBooking\CurrencyExchange\Stores\PDOStore;

class ExchangeRateManager extends Manager implements ExchangeRateProviderContract
{
    /**
     * Create an instance of the Chain exchange rate provider.
     *
     * @return ExchangeRateProvider
     */
    public function createChainDriver(): ExchangeRateProvider
    {
        /**
         * identity | round:8 | cross:RUB | chain | (
         *     rev | back:(rev|pdo:exchange_rates) | nbrb,
         *     rev | back:(rev|pdo:exchange_rates) | cbu,
         *     rev | back:(rev|pdo:exchange_rates) | cbrf
         * )
         */

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Chain([
                new Reverse(new Cache(
                    new NationalBankOfRepublicBelarus,
                    new ReverseStore(new PDOStore($this->container['db']->getPdo(), 'exchange_rates'))
                )),
                new Reverse(new Cache(
                    new CentralBankOfRepublicUzbekistan,
                    new ReverseStore(new PDOStore($this->container['db']->getPdo(), 'exchange_rates'))
                )),
                new Reverse(new Cache(
                    new RussianCentralBank,
                    new ReverseStore(new PDOStore($this->container['db']->getPdo(), 'exchange_rates'))
                )),
            ]), 'RUB')))
        );
    }

    /**
     * Create an instance of the Currency Exchange Gateway Proxy service.
     *
     * @return ExchangeRateProvider
     */
    public function createGatewayProxyDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:RUB | rev | back:(rev|cache:exchange_rates,86400) | proxy:cxwb/api/rate

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Reverse(new Cache(
                new Proxy(config: $this->config->get('currency-exchange.providers.gateway_proxy', [])),
                new ReverseStore(new CacheStore(
                    $this->container['cache.store'],
                    ['cache_key_prefix' => 'exchange_rates:', 'cache_ttl' => 86400]
                ))
            )), 'RUB')))
        );
    }

    /**
     * Create an instance of the Central Bank of Russia service.
     *
     * @return ExchangeRateProvider
     */
    public function createRussianCentralBankDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:RUB | rev | back | cbrf

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Reverse(new Cache(
                new RussianCentralBank
            )), 'RUB')))
        );
    }

    /**
     * Create an instance of the National Bank of the Republic of Belarus service.
     *
     * @return ExchangeRateProvider
     */
    public function createNationalBankOfRepublicBelarusDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:BYN | rev | back | nbrb

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Reverse(new Cache(
                new NationalBankOfRepublicBelarus
            )), 'BYN')))
        );
    }

    /**
     * Create an instance of the Central Bank of the Republic of Uzbekistan service.
     *
     * @return ExchangeRateProvider
     */
    public function createCentralBankOfRepublicUzbekistanDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:UZS | rev | back | cbu

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Reverse(new Cache(
                new CentralBankOfRepublicUzbekistan
            )), 'UZS')))
        );
    }

    /**
     * @param  ExchangeRateQueryContract|string  $query
     * @param  mixed  $date
     * @param  array  $options
     * @return bool
     */
    public function has(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): bool
    {
        return $this->provider()->has($query, $date, $options);
    }

    /**
     * @param  ExchangeRateQueryContract|string  $query
     * @param  mixed  $date
     * @param  array  $options
     * @return ExchangeRate
     *
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): ExchangeRate
    {
        return $this->provider()->get($query, $date, $options);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('currency-exchange.provider', 'chain');
    }

    /**
     * Get a provider instance.
     *
     * @param  string|null  $provider
     * @return ExchangeRateProvider
     *
     * @throws \InvalidArgumentException
     */
    public function provider(string $provider = null): ExchangeRateProvider
    {
        return $this->driver($provider);
    }
}
