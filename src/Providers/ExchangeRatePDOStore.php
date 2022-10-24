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
        $stat = $this->pdo->prepare($this->getQuery(true), [
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            $query->getDate()->format('Y-m-d'),
        ]);

        return false != $stat->fetchColumn();
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $stat = $this->pdo->prepare($this->getQuery(), [
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            $query->getDate()->format('Y-m-d'),
        ]);

        if (false === $value = $stat->fetchColumn()) {
            throw new UnsupportedExchangeQueryException;
        }

        return new ExchangeRate($query->getCurrencyPair(), $value, $query->getDate());
    }

    public function store(ExchangeRateContract $exchangeRate): ExchangeRate
    {
        $stat = $this->pdo->prepare("insert into {$this->table} (base, quote, date, service, rate) values (?, ?, ?, ?, ?)", [
            $exchangeRate->getCurrencyPair()->getBaseCurrency(),
            $exchangeRate->getCurrencyPair()->getQuoteCurrency(),
            $exchangeRate->getDate()->format('Y-m-d'),
            $exchangeRate->getServiceName(),
            $exchangeRate->getValue(),
        ]);

        try {
            $stat->execute();
        } catch (PDOException $e) {
            throw new ExchangeRateStoreException(previous: $e);
        }

        return $exchangeRate;
    }

    private function getQuery(bool $existsQuery = false): string
    {
        $query = "select rate from {$this->table} where base = ? and quote = ? and date = ? order by updated_at limit 1";

        if ($existsQuery) {
            $query = "select exists ($query)";
        }

        return $query;
    }
}
