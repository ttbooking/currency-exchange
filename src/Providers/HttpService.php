<?php

declare(strict_types=1);

namespace TTBooking\CurrencyExchange\Providers;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class HttpService extends ExchangeRateService
{
    public function __construct(
        private ?ClientInterface $httpClient = null,
        private ?RequestFactoryInterface $requestFactory = null,
    ) {
        $this->httpClient ??= HttpClientDiscovery::find();
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    private function buildRequest(string $url, array $headers = []): RequestInterface
    {
        $request = $this->requestFactory->createRequest('GET', $url);
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        return $request;
    }

    protected function request(string $url, array $headers = []): string
    {
        return (string) $this->getResponse($url, $headers)->getBody();
    }

    protected function getResponse(string $url, array $headers = []): ResponseInterface
    {
        return $this->httpClient->sendRequest($this->buildRequest($url, $headers));
    }
}
