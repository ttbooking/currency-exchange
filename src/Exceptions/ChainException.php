<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Exceptions;

use Exception;

class ChainException extends Exception
{
    /**
     * The exceptions.
     *
     * @var Exception[]
     */
    protected array $exceptions;

    /**
     * Creates a new chain exception.
     *
     * @param Exception[] $exceptions
     */
    public function __construct(array $exceptions)
    {
        $messages = array_map(function (\Throwable $exception) {
            return sprintf(
                '%s: %s',
                \get_class($exception),
                $exception->getMessage()
            );
        }, $exceptions);

        parent::__construct(
            sprintf(
                "The chain resulted in %d exception(s):\r\n%s",
                \count($exceptions),
                implode("\r\n", $messages)
            )
        );

        $this->exceptions = $exceptions;
    }

    /**
     * Gets the exceptions indexed by service class name.
     *
     * @return Exception[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
