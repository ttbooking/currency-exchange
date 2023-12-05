<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

abstract class HttpService extends ExchangeRateService
{
    public function __construct(
        protected ?ClientInterface $httpClient = null,
        protected ?RequestFactoryInterface $requestFactory = null,
    ) {
        $this->httpClient ??= Psr18ClientDiscovery::find();
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    protected function request(
        string $url,
        array $headers = [],
        string $method = 'GET',
        string|array $body = '',
    ): string {
        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        if ($body) {
            $stream = $this->streamFactory->createStream(is_array($body) ? http_build_query($body) : $body);
            $request = $request->withBody($stream);
        }

        return (string) $this->httpClient->sendRequest($request)->getBody();
    }
}
