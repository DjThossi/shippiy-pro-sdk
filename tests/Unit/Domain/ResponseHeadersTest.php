<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\RateLimit;
use DjThossi\ShippyProSdk\Domain\ResponseHeaders;
use PHPUnit\Framework\TestCase;

final class ResponseHeadersTest extends TestCase
{
    public function testGetIsCaseInsensitive(): void
    {
        $headers = new ResponseHeaders(['X-Request-Id' => 'req_1'], new RateLimit(null, null, null));

        $this->assertSame('req_1', $headers->get('x-request-id'));
        $this->assertSame('req_1', $headers->get('X-REQUEST-ID'));
    }

    public function testGetReturnsNullForUnknownHeader(): void
    {
        $headers = new ResponseHeaders([], new RateLimit(null, null, null));

        $this->assertNull($headers->get('X-Missing'));
    }

    public function testHasReportsPresence(): void
    {
        $headers = new ResponseHeaders(['ETag' => 'abc'], new RateLimit(null, null, null));

        $this->assertTrue($headers->has('etag'));
        $this->assertFalse($headers->has('X-Missing'));
    }

    public function testGetRequestId(): void
    {
        $headers = new ResponseHeaders(['X-Request-Id' => 'req_abc'], new RateLimit(null, null, null));

        $this->assertSame('req_abc', $headers->getRequestId());
    }

    public function testGetRateLimit(): void
    {
        $rateLimit = new RateLimit(100, 42, 7);
        $headers = new ResponseHeaders([], $rateLimit);

        $this->assertSame($rateLimit, $headers->getRateLimit());
    }

    public function testAllReturnsLowercasedHeaders(): void
    {
        $headers = new ResponseHeaders(['X-Request-Id' => 'req_1', 'ETag' => 'abc'], new RateLimit(null, null, null));

        $this->assertSame(['x-request-id' => 'req_1', 'etag' => 'abc'], $headers->all());
    }
}
