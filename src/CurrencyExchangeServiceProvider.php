<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use TTBooking\CurrencyExchange\Http\Controllers\ExchangeRateController;

//use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;
//use TTBooking\CurrencyExchange\Providers\Chain;
//use TTBooking\CurrencyExchange\Providers\ExchangeRateCachingDecorator;
//use TTBooking\CurrencyExchange\Providers\ExchangeRatePDOStore;
//use TTBooking\CurrencyExchange\Providers\Identity;
//use TTBooking\CurrencyExchange\Providers\NationalBankOfRepublicBelarus;
//use TTBooking\CurrencyExchange\Providers\ReversibleExchangeRateProvider;
//use TTBooking\CurrencyExchange\Providers\RussianCentralBank;

class CurrencyExchangeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
            $this->registerMigrations();
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'domain' => $this->app['config']['currency-exchange.domain'] ?? null,
            'prefix' => $this->app['config']['currency-exchange.path'] ?? null,
            'name' => 'currency-exchange.',
            'namespace' => 'TTBooking\\CurrencyExchange\\Http\\Controllers',
            'middleware' => $this->app['config']['currency-exchange.middleware'] ?? 'api',
        ], function () {
            Route::get('/rate/{base}/{quote}', [ExchangeRateController::class, 'getExchangeRate'])
                ->where(['base' => '[A-Z]{3}', 'quote' => '[A-Z]{3}']);
        });
    }

    protected function offerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/currency-exchange.php' => $this->app->configPath('currency-exchange.php'),
        ], 'currency-exchange-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
        ], 'currency-exchange-migrations');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/currency-exchange.php', 'currency-exchange');

        $this->app->singleton('currency-exchange', function ($app) {
            return new ExchangeRateManager($app);
        });

        $this->app->singleton('currency-exchange.provider', function ($app) {
            return $app['currency-exchange']->driver();
        });

        /*$this->app->singleton(ExchangeRateProvider::class, function ($app) {
            return new Identity(
                new Chain([
                    new ReversibleExchangeRateProvider(
                        new ExchangeRateCachingDecorator(
                            new NationalBankOfRepublicBelarus,
                            new ExchangeRatePDOStore($app['db']->getPdo(), 'exchange_rates')
                        )
                    ),
                    new ReversibleExchangeRateProvider(
                        new ExchangeRateCachingDecorator(
                            new RussianCentralBank,
                            new ExchangeRatePDOStore($app['db']->getPdo(), 'exchange_rates')
                        )
                    ),
                ])
            );
        });*/
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['currency-exchange', 'currency-exchange.provider'];
    }
}
