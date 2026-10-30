<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Http;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;
use DjThossi\ShippyProSdk\Http\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    /**
     * @var array<int, array{request: Request}>
     */
    private array $history = [];

    private MockHandler $mockHandler;

    private HandlerStack $handlerStack;

    protected function setUp(): void
    {
        $this->history = [];
        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);
        $this->handlerStack->push(Middleware::history($this->history));
    }

    public function testPostV1SendsBearerAuthenticatedPostWithBody(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"Result":true}'));
        $client = $this->createClient();

        $response = $client->postV1('/v1/Ping', '{"Method":"Ping","Params":{}}');

        $this->assertSame(200, $response->getStatusCode());

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.shippypro.com/v1/Ping', (string) $request->getUri());
        $this->assertSame('Bearer my-key', $request->getHeaderLine('Authorization'));
        $this->assertSame('{"Method":"Ping","Params":{}}', (string) $request->getBody());
    }

    public function testGetV2PublicSendsUnauthenticatedGet(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"status":"HEALTHY","version":"2.0.0"}'));
        $client = $this->createClient();

        $response = $client->getV2Public('/v2/status');

        $this->assertSame(200, $response->getStatusCode());

        $request = $this->lastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.shippypro.com/v2/status', (string) $request->getUri());
        $this->assertFalse($request->hasHeader('Authorization'));
    }

    public function testPartnerNameIsSentAsLowercaseRefererHeader(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"status":"HEALTHY","version":"2.0.0"}'));
        $client = $this->createClient(new PartnerName('My-Platform'));

        $client->getV2Public('/v2/status');

        $this->assertSame('my-platform', $this->lastRequest()->getHeaderLine('Referer'));
    }

    public function testNoRefererHeaderWhenPartnerNameIsNotConfigured(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"Result":true}'));
        $client = $this->createClient();

        $client->postV1('/v1/Ping', '{"Method":"Ping","Params":{}}');

        $this->assertFalse($this->lastRequest()->hasHeader('Referer'));
    }

    public function testHttpErrorsAreNotThrownForServerError(): void
    {
        $this->mockHandler->append(new Response(500, [], 'Internal Server Error'));
        $client = $this->createClient();

        $response = $client->postV1('/v1/Ping', '{"Method":"Ping","Params":{}}');

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testHttpErrorsAreNotThrownForTooManyRequests(): void
    {
        $this->mockHandler->append(new Response(429, [], ''));
        $client = $this->createClient();

        $response = $client->getV2Public('/v2/status');

        $this->assertSame(429, $response->getStatusCode());
    }

    public function testBaseUrlTrailingSlashDoesNotProduceDoubleSlash(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"Result":true}'));
        $client = $this->createClient(null, new BaseUrl('https://api.shippypro.com/'));

        $client->postV1('/v1/Ping', '{}');

        $this->assertSame('https://api.shippypro.com/v1/Ping', (string) $this->lastRequest()->getUri());
    }

    private function createClient(?PartnerName $partnerName = null, ?BaseUrl $baseUrl = null): Client
    {
        return new Client(
            $baseUrl ?? new BaseUrl('https://api.shippypro.com'),
            new ApiKey('my-key'),
            new RequestTimeout(10),
            $partnerName,
            $this->handlerStack,
        );
    }

    private function lastRequest(): Request
    {
        /** @var Request $request */
        $request = $this->history[0]['request'];

        return $request;
    }
}
