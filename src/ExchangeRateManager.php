<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Generator;
use Illuminate\Support\Manager;
use InvalidArgumentException;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery as ExchangeRateQueryContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateService;
use TTBooking\CurrencyExchange\Decorators\Cache;
use TTBooking\CurrencyExchange\Decorators\Chain;
use TTBooking\CurrencyExchange\Decorators\Cross;
use TTBooking\CurrencyExchange\Decorators\Identity;
use TTBooking\CurrencyExchange\Decorators\Reverse;
use TTBooking\CurrencyExchange\Decorators\ReverseStore;
use TTBooking\CurrencyExchange\Decorators\Round;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\Providers\BankCenterCreditKazakhstan;
use TTBooking\CurrencyExchange\Providers\CentralBankOfRepublicUzbekistan;
use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus;
use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicKazakhstan;
use TTBooking\CurrencyExchange\Providers\Proxy;
use TTBooking\CurrencyExchange\Providers\RussianCentralBank;
use TTBooking\CurrencyExchange\Stores\CacheStore;
use TTBooking\CurrencyExchange\Stores\PDOStore;

class ExchangeRateManager extends Manager implements ExchangeRateProviderContract
{
    /**
     * Create an instance of the Chain exchange rate provider.
     */
    public function createChainDriver(): ExchangeRateProvider
    {
        /**
         * identity | round:8 | cross:RUB | chain | (
         *     rev | back:(rev|pdo:exchange_rates) | nbrb,
         *     rev | back:(rev|pdo:exchange_rates) | nbrk,
         *     rev | back:(rev|pdo:exchange_rates) | cbu,
         *     rev | back:(rev|pdo:exchange_rates) | cbrf
         * )
         */

        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(
                new Chain($this->decorateServiceChain(
                    $this->config->get('currency-exchange.providers.chain.services', [])
                )),
                $this->config->get('currency-exchange.providers.chain.cross_currency', 'RUB')
            ), $this->config->get('currency-exchange.round_precision', 8)))
        );
    }

    /**
     * Create an instance of the Currency Exchange Gateway Proxy service.
     */
    public function createCurrencyExchangeGatewayDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:RUB | rev | back:(rev|cache:exchange_rates,86400) | proxy:cxgw/api/rate

        return $this->decorateService(
            Proxy::class,
            $this->config->get('currency-exchange.providers.currency_exchange_gateway.cross_currency', 'RUB'),
            cache: true
        );
    }

    /**
     * Create an instance of the Central Bank of Russia service.
     */
    public function createRussianCentralBankDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:RUB | rev | back | cbrf

        return $this->decorateService(RussianCentralBank::class, 'RUB');
    }

    /**
     * Create an instance of the National Bank of the Republic of Belarus service.
     */
    public function createNationalBankOfRepublicBelarusDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:BYN | rev | back | nbrb

        return $this->decorateService(NationalBankOfRepublicBelarus::class, 'BYN');
    }

    /**
     * Create an instance of the National Bank of the Republic of Kazakhstan service.
     */
    public function createNationalBankOfRepublicKazakhstanDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:KZT | rev | back | nbrk

        return $this->decorateService(NationalBankOfRepublicKazakhstan::class, 'KZT');
    }

    /**
     * Create an instance of the Bank Center Credit Kazakhstan service.
     */
    public function createBankCenterCreditKazakhstanDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:KZT | rev | back | bcck

        return $this->decorateService(BankCenterCreditKazakhstan::class, 'KZT');
    }

    /**
     * Create an instance of the Central Bank of the Republic of Uzbekistan service.
     */
    public function createCentralBankOfRepublicUzbekistanDriver(): ExchangeRateProvider
    {
        // identity | round:8 | cross:UZS | rev | back | cbu

        return $this->decorateService(CentralBankOfRepublicUzbekistan::class, 'UZS');
    }

    public function has(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): bool
    {
        return $this->provider()->has($query, $date, $options);
    }

    /**
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQueryContract|string $query, mixed $date = null, array $options = []): ExchangeRate
    {
        return $this->provider()->get($query, $date, $options);
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('currency-exchange.provider', 'chain');
    }

    /**
     * Get a provider instance.
     *
     *
     * @throws InvalidArgumentException
     */
    public function provider(?string $provider = null): ExchangeRateProvider
    {
        return $this->driver($provider);
    }

    /**
     * Create an instance of the specific service.
     */
    protected function createService(
        ExchangeRateService|string $service,
        ?array $config = null
    ): ExchangeRateProviderContract {
        if (! is_string($service)) {
            return $service;
        }

        try {
            return $this->provider($service);
        } catch (InvalidArgumentException) {
            $config ??= $this->config->get(
                $this->config->has($key = 'currency-exchange.providers.'.$service::getName())
                    ? $key : 'currency-exchange.providers.'.$service, []
            );

            return $this->container->make($service, compact('config'));
        }
    }

    /**
     * Create a decorated instance of the specific service.
     */
    protected function decorateService(
        ExchangeRateService|string $service,
        string $crossCurrency,
        ?array $config = null,
        bool $cache = false
    ): ExchangeRateProvider {
        return new ExchangeRateProvider(
            new Identity(new Round(new Cross(new Reverse(new Cache(
                $this->createService($service, $config),
                $cache
                    ? new ReverseStore(new CacheStore(
                        $this->container['cache.store'],
                        $this->config->get('currency-exchange.stores.cache', [])
                    ))
                    : null
            )), $crossCurrency), $this->config->get('currency-exchange.round_precision', 8)))
        );
    }

    /**
     * @param  iterable<int|class-string<ExchangeRateService>, ExchangeRateService|string|array>  $services
     * @return Generator<int, ExchangeRateProviderContract>
     */
    protected function decorateServiceChain(iterable $services): Generator
    {
        foreach ($services as $service => $config) {
            yield new Reverse(new Cache(
                is_array($config)
                    ? $this->createService($service, $config)
                    : $this->createService($config),
                new ReverseStore(new PDOStore(
                    $this->container['db']->getPdo(),
                    $this->config->get('currency-exchange.stores.database.table', 'exchange_rates')
                ))
            ));
        }
    }
}
