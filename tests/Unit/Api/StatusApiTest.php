<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Api;

use DjThossi\ShippyProSdk\Api\StatusApi;
use DjThossi\ShippyProSdk\Domain\StatusResponse;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\StatusMapper;
use GuzzleHttp\Psr7\Response;

final class StatusApiTest extends ApiTestCase
{
    public function testStatusSendsUnauthenticatedGetToTheStatusEndpoint(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('getV2Public')
            ->with('/v2/status')
            ->willReturn(new Response(200, [], '{"status":"HEALTHY","version":"2.0.0"}'));

        $this->createApi($client)->status();
    }

    public function testStatusNeverGoesThroughTheAuthenticatedPostPath(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('postV1');
        $client->method('getV2Public')
            ->willReturn(new Response(200, [], '{"status":"HEALTHY","version":"2.0.0"}'));

        $this->createApi($client)->status();
    }

    public function testStatusMapsSuccessfulResponse(): void
    {
        $result = $this->statusFor(new Response(200, [], '{"status":"HEALTHY","version":"2.0.0"}'));

        $this->assertInstanceOf(StatusResponse::class, $result);
        $this->assertSame(200, $result->statusCode->value);
        $this->assertTrue($result->statusCode->isOk());
        $this->assertNotNull($result->responseBody);
        $this->assertSame('HEALTHY', $result->responseBody->status);
        $this->assertSame('2.0.0', $result->responseBody->version);
        $this->assertFalse($result->hasError());
        $this->assertNull($result->error);
    }

    public function testStatusExposesResponseHeaders(): void
    {
        $result = $this->statusFor(new Response(
            200,
            [
                'X-Request-Id' => 'req_abc123',
                'X-RateLimit-Limit' => '100',
                'X-RateLimit-Remaining' => '42',
                'X-RateLimit-Reset' => '1776685072',
            ],
            '{"status":"HEALTHY","version":"2.0.0"}',
        ));

        $this->assertSame('req_abc123', $result->headers->getRequestId());
        $this->assertSame(100, $result->headers->getRateLimit()->limit);
        $this->assertSame(42, $result->headers->getRateLimit()->remaining);
        $this->assertSame(1776685072, $result->headers->getRateLimit()->reset);
    }

    public function testStatusSurfacesProblemDetailError(): void
    {
        $body = '{"type":"https://api.shippypro.com/problems/unauthorized","title":"Unauthorized",'
            . '"status":401,"detail":"Authentication failed"}';

        $result = $this->statusFor(new Response(401, [], $body));

        $this->assertSame(401, $result->statusCode->value);
        $this->assertNull($result->responseBody);
        $this->assertTrue($result->hasError());
        $this->assertSame('Authentication failed', $result->error->detail);
        $this->assertSame('Unauthorized', $result->error->title);
        $this->assertSame(401, $result->error->status);
    }

    public function testStatusWithEmptyBodyOnErrorYieldsNoBodyAndNoError(): void
    {
        $result = $this->statusFor(new Response(503, [], ''));

        $this->assertSame(503, $result->statusCode->value);
        $this->assertNull($result->responseBody);
        $this->assertFalse($result->hasError());
    }

    public function testStatusWithNonObjectJsonBodyIsTreatedAsEmpty(): void
    {
        // A JSON scalar/null body decodes to a non-array; it carries no status
        // fields and no error indicator.
        $result = $this->statusFor(new Response(500, [], 'null'));

        $this->assertSame(500, $result->statusCode->value);
        $this->assertNull($result->responseBody);
        $this->assertFalse($result->hasError());
    }

    private function statusFor(Response $response): StatusResponse
    {
        $client = $this->createStub(Client::class);
        $client->method('getV2Public')->willReturn($response);

        return $this->createApi($client)->status();
    }

    private function createApi(Client $client): StatusApi
    {
        return new StatusApi(
            $client,
            new StatusMapper(),
            new HeaderMapper(),
            new ErrorMapper(),
        );
    }
}
