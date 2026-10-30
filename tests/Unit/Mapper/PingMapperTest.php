<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Mapper;

use DjThossi\ShippyProSdk\Domain\PingResponseBody;
use DjThossi\ShippyProSdk\Mapper\PingMapper;

use function json_decode;

use const JSON_THROW_ON_ERROR;

use PHPUnit\Framework\TestCase;
use stdClass;

final class PingMapperTest extends TestCase
{
    private PingMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PingMapper();
    }

    public function testEncodeRequestProducesTheExpectedEnvelope(): void
    {
        $json = $this->mapper->encodeRequest();

        $this->assertSame('{"Method":"Ping","Params":{}}', $json);
    }

    public function testEncodeRequestParamsIsAnEmptyJsonObjectNotArray(): void
    {
        $decoded = json_decode($this->mapper->encodeRequest(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Ping', $decoded->Method);
        $this->assertIsObject($decoded->Params);
        $this->assertEquals(new stdClass(), $decoded->Params);
    }

    public function testMapResponseBodyWithTrueResult(): void
    {
        $body = $this->mapper->mapResponseBody(['Result' => true]);

        $this->assertInstanceOf(PingResponseBody::class, $body);
        $this->assertTrue($body->result);
    }

    public function testMapResponseBodyWithFalseResult(): void
    {
        $body = $this->mapper->mapResponseBody(['Result' => false]);

        $this->assertFalse($body->result);
    }

    public function testMapResponseBodyMissingResultYieldsNull(): void
    {
        $body = $this->mapper->mapResponseBody([]);

        $this->assertInstanceOf(PingResponseBody::class, $body);
        $this->assertNull($body->result);
    }

    public function testMapResponseBodyWithAuthenticationErrorBodyYieldsNull(): void
    {
        // ShippyPro reports a failed Ping with HTTP 200 and this body shape
        // instead of the documented 401, carrying no `Result` field.
        $body = $this->mapper->mapResponseBody(['Error' => 'Authentication Failed']);

        $this->assertNull($body->result);
    }
}
