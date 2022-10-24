<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Support\Manager;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\Providers\Chain;
use TTBooking\CurrencyExchange\Providers\ExchangeRateCachingDecorator;
use TTBooking\CurrencyExchange\Providers\ExchangeRatePDOStore;
use TTBooking\CurrencyExchange\Providers\GatewayProxy;
use TTBooking\CurrencyExchange\Providers\Identity;
use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus;
use TTBooking\CurrencyExchange\Providers\ReversibleExchangeRateProvider;
use TTBooking\CurrencyExchange\Providers\RussianCentralBank;

class ExchangeRateManager extends Manager implements ExchangeRateProvider
{
    /**
     * Create an instance of the Chain exchange rate provider.
     *
     * @return ExchangeRateProvider
     */
    public function createChainDriver(): ExchangeRateProvider
    {
        return new Identity(
            new Chain([
                new ReversibleExchangeRateProvider(
                    new ExchangeRateCachingDecorator(
                        new NationalBankOfRepublicBelarus,
                        new ExchangeRatePDOStore($this->container['db']->getPdo(), 'exchange_rates')
                    )
                ),
                new ReversibleExchangeRateProvider(
                    new ExchangeRateCachingDecorator(
                        new RussianCentralBank,
                        new ExchangeRatePDOStore($this->container['db']->getPdo(), 'exchange_rates')
                    )
                ),
            ])
        );
    }

    /**
     * Create an instance of the Currency Exchange Gateway Proxy service.
     *
     * @return ExchangeRateProvider
     */
    public function createGatewayProxyDriver(): ExchangeRateProvider
    {
        return new Identity(
            new ReversibleExchangeRateProvider(
                new ExchangeRateCachingDecorator(
                    new GatewayProxy,
                )
            )
        );
    }

    /**
     * Create an instance of the Central Bank of Russia service.
     *
     * @return ExchangeRateProvider
     */
    public function createRussianCentralBankDriver(): ExchangeRateProvider
    {
        return new Identity(
            new ReversibleExchangeRateProvider(
                new ExchangeRateCachingDecorator(
                    new RussianCentralBank,
                )
            )
        );
    }

    /**
     * Create an instance of the National Bank of the Republic of Belarus service.
     *
     * @return ExchangeRateProvider
     */
    public function createNationalBankOfRepublicBelarusDriver(): ExchangeRateProvider
    {
        return new Identity(
            new ReversibleExchangeRateProvider(
                new ExchangeRateCachingDecorator(
                    new NationalBankOfRepublicBelarus,
                )
            )
        );
    }

    /**
     * @param ExchangeRateQuery $query
     * @return bool
     */
    public function has(ExchangeRateQuery $query): bool
    {
        return $this->driver()->has($query);
    }

    /**
     * @param ExchangeRateQuery $query
     * @return ExchangeRate
     *
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        return $this->driver()->get($query);
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
