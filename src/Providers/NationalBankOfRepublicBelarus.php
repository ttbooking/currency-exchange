<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use TTBooking\CurrencyExchange\Contracts\ExchangeRateQuery;
use TTBooking\CurrencyExchange\Exceptions\UnsupportedExchangeQueryException;
use TTBooking\CurrencyExchange\ExchangeRate;
use TTBooking\CurrencyExchange\StringUtil;

class NationalBankOfRepublicBelarus extends HttpService
{
    protected const URL = 'https://www.nbrb.by/api/exrates/rates';

    public function has(ExchangeRateQuery $query, bool $ignoreSupportPeriod = false): bool
    {
        $currencyPair = $query->getCurrencyPair();
        $baseCurrency = $currencyPair->getBaseCurrency();
        $quoteCurrency = $currencyPair->getQuoteCurrency();
        $date = !$ignoreSupportPeriod ? $query->getDate() : null;

        return is_int(self::detectPeriodicity($baseCurrency, $date))
            && self::supportQuoteCurrency($quoteCurrency, $date);
    }

    /**
     * Tells if the service supports base currency for the given date and detect its periodicity if it does.
     *
     * @param string $baseCurrency
     * @param \DateTimeInterface|null $date
     *
     * @return int|false
     */
    private static function detectPeriodicity(string $baseCurrency, \DateTimeInterface $date = null): int|false
    {
        return array_reduce(

            array_reverse(array_intersect_key(
                $codes = self::getSupportedCodes(),
                array_flip(array_keys(array_column($codes, 'Cur_Abbreviation'), $baseCurrency))
            )),

            static function ($periodicity, $entry) use ($date) {
                if ($date) {
                    $dateStart = new \DateTimeImmutable($entry['Cur_DateStart']);
                    $dateEnd = new \DateTimeImmutable($entry['Cur_DateEnd']);
                    if ($date < $dateStart || $date > $dateEnd) {
                        return $periodicity;
                    }
                }

                return in_array($periodicity, [false, 1], true) ? $entry['Cur_Periodicity'] : $periodicity;
            },

            false

        );
    }

    /**
     * Tells if the service supports quote currency for the given date.
     *
     * @param string $quoteCurrency
     * @param \DateTimeInterface|null $date
     *
     * @return bool
     */
    private static function supportQuoteCurrency(string $quoteCurrency, \DateTimeInterface $date = null): bool
    {
        if ($date) {
            $date = $date->format('Y-m-d');
        }

        return $date
            ? $quoteCurrency === 'BYN' && $date >= '2016-07-01'
            || $quoteCurrency === 'BYR' && $date >= '2000-01-01' && $date < '2016-07-01'
            || $quoteCurrency === 'BYB' && $date >= '1992-05-25' && $date < '2000-01-01'
            : in_array($quoteCurrency, ['BYN', 'BYR', 'BYB']);
    }

    /**
     * Array of base currency codes supported by the service.
     *
     * @url https://www.nbrb.by/api/exrates/currencies
     *
     * @return list<array{
     *     Cur_Abbreviation: string,
     *     Cur_Periodicity: int,
     *     Cur_DateStart: string,
     *     Cur_DateEnd: string
     * }>
     */
    private static function getSupportedCodes(): array
    {
        static $codes;

        return $codes = $codes ?? StringUtil::jsonToArray(file_get_contents(__DIR__.'/../../resources/nbrb-codes.json'));
    }

    /**
     * Creates the rate.
     *
     * @param ExchangeRateQuery $query
     * @return ExchangeRate
     *
     * @throws UnsupportedExchangeQueryException
     */
    public function get(ExchangeRateQuery $query): ExchangeRate
    {
        $currencyPair = $query->getCurrencyPair();
        $baseCurrency = $currencyPair->getBaseCurrency();

        if (!$this->has($query, true)) {
            throw new UnsupportedExchangeQueryException;
        }

        if ($query->getDate()->format('Y-m-d') < '1995-03-29') {
            throw new UnsupportedExchangeQueryException;
        }

        $content = $this->request($this->buildUrl($baseCurrency, $query->getDate()));
        $result = StringUtil::jsonToArray($content);
        $entryId = array_search($baseCurrency, array_column($result, 'Cur_Abbreviation'));

        if ($entryId === false) {
            throw new UnsupportedExchangeQueryException;
        }

        /**
         * @var array{
         *     Cur_ID: int,
         *     Date: string,
         *     Cur_Abbreviation: string,
         *     Cur_Scale: int,
         *     Cur_Name: string,
         *     Cur_OfficialRate: float
         * } $entry
         */
        $entry = $result[$entryId];

        if (!isset($entry['Cur_OfficialRate'])) {
            throw new \RuntimeException('Service has returned malformed response');
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $entry['Date'] ?? null);
        if (!$date) {
            throw new UnsupportedExchangeQueryException;
        }

        $rate = $entry['Cur_OfficialRate'];
        $scale = $entry['Cur_Scale'] ?? 1;

        return $this->createRate($currencyPair, $rate / $scale, $date, $query->getDate());
    }

    public function getName(): string
    {
        return 'national_bank_of_republic_belarus';
    }

    /**
     * Builds the url.
     *
     * @param string $baseCurrency
     * @param \DateTimeInterface $requestedDate
     *
     * @return string
     */
    private function buildUrl(string $baseCurrency, \DateTimeInterface $requestedDate): string
    {
        return static::URL.'?'.http_build_query([
            'ondate' => $requestedDate->format('Y-m-d'),
            'periodicity' => (int) self::detectPeriodicity($baseCurrency, $requestedDate),
        ]);
    }
}
