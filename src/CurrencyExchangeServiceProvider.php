<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateProvider;

class CurrencyExchangeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }
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
            return $app['currency-exchange']->provider();
        });

        $this->app->bind(ExchangeRateProvider::class, 'currency-exchange.provider');
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
