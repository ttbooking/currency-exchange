<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Support\ServiceProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
use TTBooking\CurrencyExchange\Providers\Chain;
use TTBooking\CurrencyExchange\Providers\ExchangeRateCachingDecorator;
use TTBooking\CurrencyExchange\Providers\ExchangeRatePDOStore;
use TTBooking\CurrencyExchange\Providers\Identity;
use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus;
use TTBooking\CurrencyExchange\Providers\ReversibleExchangeRateProvider;
use TTBooking\CurrencyExchange\Providers\RussianCentralBank;

class CurrencyExchangeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // TODO
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ExchangeRateProvider::class, function () {
            return new Chain([
                new NationalBankOfRepublicBelarus,
                new RussianCentralBank,
            ]);
        });

        $this->app->extend(ExchangeRateProvider::class, function (ExchangeRateProvider $provider, $app) {
            return new Identity(
                new ReversibleExchangeRateProvider(
                    new ExchangeRateCachingDecorator(
                        $provider, new ExchangeRatePDOStore($app['db']->getPdo(), 'exchange_rates')
                    )
                )
            );
        });
    }
}
