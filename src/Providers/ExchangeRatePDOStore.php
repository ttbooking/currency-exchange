<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use PDO, PDOException;
use TTBooking\CurrencyExchange\Contracts\ExchangeRate as ExchangeRateContract;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Contracts\ExchangeRateStore;
use TTBooking\CurrencyExchange\Exceptions\ExchangeRateStoreException;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;

class ExchangeRatePDOStore implements ExchangeRateStore
{
    public function __construct(protected PDO $pdo, protected string $table)
    {
    }

    public function has(ExchangeRateQuery $query): bool
    {
        $service = $query->getOption('service');

        $stat = $this->pdo->prepare($this->getQuery(isset($service), true));

        $stat->execute(array_filter([
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            $query->getDate()->format('Y-m-d'),
            $service,
        ]));

        $result = $stat->fetch();

        return (bool) $result[0] ?? false;
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $service = $query->getOption('service');

        $stat = $this->pdo->prepare($this->getQuery(isset($service)));

        $stat->execute(array_filter([
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            $query->getDate()->format('Y-m-d'),
            $service,
        ]));

        $result = $stat->fetch();

        if (false === $value = $result[0] ?? false) {
            throw new UnsupportedExchangeQueryException;
        }

        return new ExchangeRate($query->getCurrencyPair(), (float) $value, $query->getDate(), $result[1] ?? null);
    }

    public function store(ExchangeRateContract $exchangeRate): ExchangeRate
    {
        $stat = $this->pdo->prepare("insert into {$this->table} (base, quote, date, service, rate) values (?, ?, ?, ?, ?)");

        //try {
            $stat->execute([
                $exchangeRate->getCurrencyPair()->getBaseCurrency(),
                $exchangeRate->getCurrencyPair()->getQuoteCurrency(),
                $exchangeRate->getDate()->format('Y-m-d'),
                $exchangeRate->getServiceName(),
                $exchangeRate->getValue(),
            ]);
        //} catch (PDOException $e) {
        //    throw new ExchangeRateStoreException(previous: $e);
        //}

        return $exchangeRate;
    }

    private function getQuery(bool $serviceSpecified, bool $existsQuery = false): string
    {
        $serviceQuery = $serviceSpecified ? ' and service = ?' : '';
        $query = "select rate, service from {$this->table} where base = ? and quote = ? and date = ?{$serviceQuery} order by created_at limit 1";

        if ($existsQuery) {
            $query = "select exists ($query) as `exists`";
        }

        return $query;
    }
}
