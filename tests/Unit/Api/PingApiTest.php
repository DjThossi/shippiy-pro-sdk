<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Api;

use DjThossi\ShippyProSdk\Api\PingApi;
use DjThossi\ShippyProSdk\Domain\PingResponse;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\PingMapper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;

final class PingApiTest extends ApiTestCase
{
    public function testPingSendsEnvelopePostToThePingEndpoint(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('postV1')
            ->with('/v1/Ping', '{"Method":"Ping","Params":{}}')
            ->willReturn(new Response(200, [], '{"Result":true}'));

        $this->createApi($client)->ping();
    }

    public function testPingNeverGoesThroughThePublicGetPath(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('getV2Public');
        $client->method('postV1')
            ->willReturn(new Response(200, [], '{"Result":true}'));

        $this->createApi($client)->ping();
    }

    public function testPingMapsSuccessfulTrueResult(): void
    {
        $result = $this->pingFor(new Response(200, [], '{"Result":true}'));

        $this->assertInstanceOf(PingResponse::class, $result);
        $this->assertSame(200, $result->statusCode->value);
        $this->assertTrue($result->statusCode->isOk());
        $this->assertTrue($result->statusCode->isSuccessful());
        $this->assertTrue($result->responseBody->result);
        $this->assertFalse($result->hasError());
        $this->assertNull($result->error);
    }

    public function testPingExposesResponseHeaders(): void
    {
        $result = $this->pingFor(new Response(
            200,
            ['X-Request-Id' => 'req_ping_1', 'X-RateLimit-Remaining' => '9'],
            '{"Result":true}',
        ));

        $this->assertSame('req_ping_1', $result->headers->getRequestId());
        $this->assertSame(9, $result->headers->getRateLimit()->remaining);
    }

    public function testPingMapsSuccessfulFalseResult(): void
    {
        $result = $this->pingFor(new Response(200, [], '{"Result":false}'));

        $this->assertTrue($result->statusCode->isOk());
        $this->assertFalse($result->responseBody->result);
    }

    #[DataProvider('errorCodeProvider')]
    public function testPingSurfacesErrorCodeThroughStatusCode(int $errorCode, string $helper): void
    {
        $result = $this->pingFor(new Response($errorCode, [], ''));

        $this->assertSame($errorCode, $result->statusCode->value);
        $this->assertFalse($result->statusCode->isSuccessful());
        $this->assertNull($result->responseBody->result);
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

    public function testPingExposes401Unauthorized(): void
    {
        $result = $this->pingFor(new Response(401, [], ''));

        $this->assertSame(401, $result->statusCode->value);
        $this->assertFalse($result->statusCode->isSuccessful());
    }

    public function testPingWithAuthenticationErrorBodyOn200DoesNotThrowAndYieldsNullResult(): void
    {
        // ShippyPro returns HTTP 200 with an {"Error": ...} body (and no
        // "Result" field) when authentication fails. This must not throw;
        // the absent result surfaces as null.
        $result = $this->pingFor(new Response(200, [], '{"Error":"Authentication Failed"}'));

        $this->assertSame(200, $result->statusCode->value);
        $this->assertNull($result->responseBody->result);
        $this->assertTrue($result->hasError());
        $this->assertSame('Authentication Failed', $result->error->detail);
    }

    private function pingFor(Response $response): PingResponse
    {
        $client = $this->createStub(Client::class);
        $client->method('postV1')->willReturn($response);

        return $this->createApi($client)->ping();
    }

    private function createApi(Client $client): PingApi
    {
        return new PingApi(
            $client,
            new PingMapper(),
            new HeaderMapper(),
            new ErrorMapper(),
        );
    }
}
