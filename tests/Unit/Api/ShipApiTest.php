<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Api;

use DjThossi\ShippyProSdk\Api\ShipApi;
use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Domain\ShipResponse;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\ShipMapper;
use GuzzleHttp\Psr7\Response;

use const JSON_THROW_ON_ERROR;

use PHPUnit\Framework\Attributes\DataProvider;

final class ShipApiTest extends ApiTestCase
{
    public function testShipSendsBearerEnvelopePostToTheShipEndpoint(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('postV1')
            ->with(
                '/v1/Ship',
                $this->callback(static function (string $body): bool {
                    $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

                    return $decoded['Method'] === 'Ship'
                        && isset($decoded['Params']['TransactionID'])
                        && $decoded['Params']['TransactionID'] === 'ORDER1';
                }),
            )
            ->willReturn(new Response(200, [], '{"Result":"OK","Status":"1"}'));

        $this->createApi($client)->ship($this->request());
    }

    public function testShipNeverGoesThroughThePublicGetPath(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('getV2Public');
        $client->method('postV1')->willReturn(new Response(200, [], '{"Result":"OK","Status":"1"}'));

        $this->createApi($client)->ship($this->request());
    }

    public function testShipMapsSuccessfulResponse(): void
    {
        $result = $this->shipFor(new Response(
            200,
            ['X-Request-Id' => 'req_ship_1'],
            '{"Result":"OK","Status":"1","NewOrderID":"999","LabelURL":"https://cdn/label.pdf"}',
        ));

        $this->assertInstanceOf(ShipResponse::class, $result);
        $this->assertSame(200, $result->statusCode->value);
        $this->assertTrue($result->statusCode->isOk());
        $this->assertFalse($result->hasError());
        $this->assertNotNull($result->responseBody);
        $this->assertSame('999', $result->responseBody->newOrderId);
        $this->assertSame(['https://cdn/label.pdf'], $result->responseBody->labelUrls->all());
        $this->assertSame('req_ship_1', $result->headers->getRequestId());
    }

    public function testShipExposesBusinessErrorOn200(): void
    {
        $result = $this->shipFor(new Response(
            200,
            [],
            '{"Result":"KO","Status":"2","ErrorMessage":"Invalid address",'
            . '"ErrorType":{"code":"ADDRESS_INVALID","name":"Invalid address","typeDescription":"desc"},'
            . '"ErrorSuggestion":{"errorDescription":"too long","solution":"shorten"}}',
        ));

        $this->assertSame(200, $result->statusCode->value);
        $this->assertNotNull($result->responseBody);
        $this->assertSame(ShipStatus::Error, $result->responseBody->status);
        $this->assertTrue($result->responseBody->hasBusinessError());
        $this->assertSame('Invalid address', $result->responseBody->errorMessage);
        $this->assertSame('ADDRESS_INVALID', $result->responseBody->errorType?->code);
        $this->assertSame('shorten', $result->responseBody->errorSuggestion?->solution);
    }

    #[DataProvider('errorCodeProvider')]
    public function testShipSurfacesHttpErrorCodeThroughStatusCode(int $code, string $helper): void
    {
        $result = $this->shipFor(new Response($code, [], ''));

        $this->assertSame($code, $result->statusCode->value);
        $this->assertFalse($result->statusCode->isSuccessful());
        $this->assertNull($result->responseBody);
        $this->assertTrue($result->statusCode->{$helper}());
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function errorCodeProvider(): array
    {
        return [
            '400 bad request' => [400, 'isBadRequest'],
            '403 forbidden' => [403, 'isForbidden'],
            '404 not found' => [404, 'isNotFound'],
            '429 too many requests' => [429, 'isTooManyRequests'],
            '500 server error' => [500, 'isServerError'],
        ];
    }

    public function testShipExposes401Unauthorized(): void
    {
        $result = $this->shipFor(new Response(401, [], ''));

        $this->assertSame(401, $result->statusCode->value);
        $this->assertFalse($result->statusCode->isSuccessful());
        $this->assertNull($result->responseBody);
    }

    public function testShipNormalizesV1ErrorBodyIntoApiError(): void
    {
        $result = $this->shipFor(new Response(200, [], '{"Error":"Authentication Failed"}'));

        $this->assertTrue($result->hasError());
        $this->assertSame('Authentication Failed', $result->error?->detail);
        $this->assertNull($result->responseBody);
    }

    private function shipFor(Response $response): ShipResponse
    {
        $client = $this->createStub(Client::class);
        $client->method('postV1')->willReturn($response);

        return $this->createApi($client)->ship($this->request());
    }

    private function createApi(Client $client): ShipApi
    {
        return new ShipApi($client, new ShipMapper(), new HeaderMapper(), new ErrorMapper());
    }

    private function request(): ShipRequest
    {
        $address = new Address('N', '', 'S1', '', 'City', 'ST', '1', new CountryCode('US'), '1', 'a@b.c');

        return new ShipRequest(
            $address,
            $address,
            new ParcelCollection(new Parcel(5.0, 5.0, 5.0, 10.0)),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
        );
    }
}
