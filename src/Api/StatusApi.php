<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Api;

use DjThossi\ShippyProSdk\Domain\StatusCode;
use DjThossi\ShippyProSdk\Domain\StatusResponse;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\StatusMapper;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class StatusApi
{
    private const string ENDPOINT = '/v2/status';

    public function __construct(
        private Client $client,
        private StatusMapper $mapper,
        private HeaderMapper $headerMapper,
        private ErrorMapper $errorMapper,
    ) {
    }

    public function status(): StatusResponse
    {
        $response = $this->client->getV2Public(self::ENDPOINT);
        $statusCode = new StatusCode($response->getStatusCode());
        $headers = $this->headerMapper->mapResponseHeaders($response);

        $data = $this->decodeBody((string) $response->getBody());

        $error = $this->errorMapper->mapFromArray($data);
        if ($error !== null || !$statusCode->isOk()) {
            return new StatusResponse($statusCode, $headers, null, $error);
        }

        return new StatusResponse($statusCode, $headers, $this->mapper->mapResponseBody($data));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBody(string $body): array
    {
        if ($body === '') {
            return [];
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }
}
