<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Http;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;

use function ltrim;

use Psr\Http\Message\ResponseInterface;

use function sprintf;

class Client
{
    private const string HEADER_AUTHORIZATION = 'Authorization';
    private const string HEADER_REFERER = 'Referer';

    private readonly GuzzleClient $client;
    private readonly string $baseUrl;
    private readonly ApiKey $apiKey;
    private readonly ?PartnerName $partnerName;

    public function __construct(
        BaseUrl $baseUrl,
        ApiKey $apiKey,
        RequestTimeout $requestTimeout,
        ?PartnerName $partnerName = null,
        ?HandlerStack $handlerStack = null,
    ) {
        $this->baseUrl = $baseUrl->value;
        $this->apiKey = $apiKey;
        $this->partnerName = $partnerName;

        $config = [
            'timeout' => $requestTimeout->seconds,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($handlerStack instanceof HandlerStack) {
            $config['handler'] = $handlerStack;
        }

        $this->client = new GuzzleClient($config);
    }

    public function postV1(string $endpoint, string $jsonBody): ResponseInterface
    {
        return $this->client->post($this->buildUrl($endpoint), [
            'headers' => $this->withPartnerHeader([
                self::HEADER_AUTHORIZATION => sprintf('Bearer %s', $this->apiKey->value),
            ]),
            'body' => $jsonBody,
        ]);
    }

    public function getV2Public(string $endpoint): ResponseInterface
    {
        return $this->client->get($this->buildUrl($endpoint), [
            'headers' => $this->withPartnerHeader([]),
        ]);
    }

    private function buildUrl(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function withPartnerHeader(array $headers): array
    {
        if ($this->partnerName instanceof PartnerName) {
            $headers[self::HEADER_REFERER] = $this->partnerName->value;
        }

        return $headers;
    }
}
