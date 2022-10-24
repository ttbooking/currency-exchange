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
        $stat = $this->pdo->prepare($this->getQuery(true));

        $stat->execute([
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            ($query->getDate() ?? new \DateTime)->format('Y-m-d'),
        ]);

        $result = $stat->fetch();

        return (bool) $result[0] ?? false;
    }

    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $stat = $this->pdo->prepare($this->getQuery());

        $stat->execute([
            $query->getCurrencyPair()->getBaseCurrency(),
            $query->getCurrencyPair()->getQuoteCurrency(),
            ($query->getDate() ?? new \DateTime)->format('Y-m-d'),
        ]);

        $result = $stat->fetch();

        if (false === $value = $result[0] ?? false) {
            throw new UnsupportedExchangeQueryException;
        }

        return new ExchangeRate($query->getCurrencyPair(), (float) $value, $query->getDate());
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

    private function getQuery(bool $existsQuery = false): string
    {
        $query = "select rate from {$this->table} where base = ? and quote = ? and date = ? order by created_at limit 1";

        if ($existsQuery) {
            $query = "select exists ($query) as `exists`";
        }

        return $query;
    }
}
