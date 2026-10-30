<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Api;

use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Domain\ShipResponse;
use DjThossi\ShippyProSdk\Domain\StatusCode;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\ShipMapper;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class ShipApi
{
    private const string ENDPOINT = '/v1/Ship';

    public function __construct(
        private Client $client,
        private ShipMapper $mapper,
        private HeaderMapper $headerMapper,
        private ErrorMapper $errorMapper,
    ) {
    }

    public function ship(ShipRequest $request): ShipResponse
    {
        $response = $this->client->postV1(self::ENDPOINT, $this->mapper->encodeRequest($request));
        $statusCode = new StatusCode($response->getStatusCode());
        $headers = $this->headerMapper->mapResponseHeaders($response);

        $data = $this->decodeBody((string) $response->getBody());

        $error = $this->errorMapper->mapFromArray($data);
        if ($error !== null || !$statusCode->isOk()) {
            return new ShipResponse($statusCode, $headers, null, $error);
        }

        return new ShipResponse($statusCode, $headers, $this->mapper->mapResponseBody($data));
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
