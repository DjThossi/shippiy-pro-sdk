<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Mapper;

use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class HeaderMapperTest extends TestCase
{
    private HeaderMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new HeaderMapper();
    }

    public function testMapsRequestIdAndLegacyRateLimitHeaders(): void
    {
        $response = new Response(200, [
            'X-Request-Id' => 'req_abc123',
            'X-RateLimit-Limit' => '100',
            'X-RateLimit-Remaining' => '42',
            'X-RateLimit-Reset' => '1776685072',
        ]);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertSame('req_abc123', $headers->getRequestId());
        $this->assertSame(100, $headers->getRateLimit()->limit);
        $this->assertSame(42, $headers->getRateLimit()->remaining);
        $this->assertSame(1776685072, $headers->getRateLimit()->reset);
    }

    public function testFallsBackToIetfRateLimitHeaders(): void
    {
        $response = new Response(200, [
            'RateLimit-Policy' => '"standard";q=100;w=60',
            'RateLimit' => '"standard";r=42;t=18',
        ]);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertSame(100, $headers->getRateLimit()->limit);
        $this->assertSame(42, $headers->getRateLimit()->remaining);
        $this->assertSame(18, $headers->getRateLimit()->reset);
    }

    public function testLegacyHeadersTakePrecedenceOverIetfHeaders(): void
    {
        $response = new Response(200, [
            'X-RateLimit-Remaining' => '5',
            'RateLimit' => '"standard";r=42;t=18',
        ]);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertSame(5, $headers->getRateLimit()->remaining);
        $this->assertSame(18, $headers->getRateLimit()->reset);
    }

    public function testNoRateLimitHeadersYieldsEmptyRateLimit(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json']);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertFalse($headers->getRateLimit()->hasData());
        $this->assertNull($headers->getRequestId());
    }

    public function testNonNumericLegacyHeaderIsIgnored(): void
    {
        $response = new Response(200, ['X-RateLimit-Limit' => 'not-a-number']);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertNull($headers->getRateLimit()->limit);
    }

    public function testMalformedIetfHeaderYieldsNull(): void
    {
        $response = new Response(200, ['RateLimit' => '"standard"']);

        $headers = $this->mapper->mapResponseHeaders($response);

        $this->assertNull($headers->getRateLimit()->remaining);
        $this->assertNull($headers->getRateLimit()->reset);
    }
}
