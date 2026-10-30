<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Api;

use DjThossi\ShippyProSdk\Domain\PingResponse;
use DjThossi\ShippyProSdk\Domain\StatusCode;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\PingMapper;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class PingApi
{
    private const string ENDPOINT = '/v1/Ping';

    public function __construct(
        private Client $client,
        private PingMapper $mapper,
        private HeaderMapper $headerMapper,
        private ErrorMapper $errorMapper,
    ) {
    }

    public function ping(): PingResponse
    {
        $response = $this->client->postV1(self::ENDPOINT, $this->mapper->encodeRequest());
        $statusCode = new StatusCode($response->getStatusCode());
        $headers = $this->headerMapper->mapResponseHeaders($response);

        $data = $this->decodeBody((string) $response->getBody());
        $error = $this->errorMapper->mapFromArray($data);

        // The `Result` field is optional (v1.json). It is null when absent,
        // e.g. when authentication fails and ShippyPro answers HTTP 200 with
        // an {"Error": ...} body. The failure is still readable via $error and
        // the StatusCode.
        $responseBody = $this->mapper->mapResponseBody($data);

        return new PingResponse($statusCode, $headers, $responseBody, $error);
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
