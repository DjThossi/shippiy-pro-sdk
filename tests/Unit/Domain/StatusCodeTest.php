<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\StatusCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusCodeTest extends TestCase
{
    public function testStoresValue(): void
    {
        $statusCode = new StatusCode(200);

        $this->assertSame(200, $statusCode->value);
    }

    #[DataProvider('successfulProvider')]
    public function testIsSuccessful(int $value, bool $expected): void
    {
        $this->assertSame($expected, (new StatusCode($value))->isSuccessful());
    }

    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function successfulProvider(): array
    {
        return [
            '199 not successful' => [199, false],
            '200 successful' => [200, true],
            '204 successful' => [204, true],
            '299 successful' => [299, true],
            '300 not successful' => [300, false],
            '404 not successful' => [404, false],
            '500 not successful' => [500, false],
        ];
    }

    public function testIsOk(): void
    {
        $this->assertTrue((new StatusCode(200))->isOk());
        $this->assertFalse((new StatusCode(201))->isOk());
    }

    public function testIsBadRequest(): void
    {
        $this->assertTrue((new StatusCode(400))->isBadRequest());
        $this->assertFalse((new StatusCode(200))->isBadRequest());
    }

    public function testIsForbidden(): void
    {
        $this->assertTrue((new StatusCode(403))->isForbidden());
        $this->assertFalse((new StatusCode(401))->isForbidden());
    }

    public function testIsNotFound(): void
    {
        $this->assertTrue((new StatusCode(404))->isNotFound());
        $this->assertFalse((new StatusCode(400))->isNotFound());
    }

    public function testIsTooManyRequests(): void
    {
        $this->assertTrue((new StatusCode(429))->isTooManyRequests());
        $this->assertFalse((new StatusCode(200))->isTooManyRequests());
    }

    public function testIsServerError(): void
    {
        $this->assertTrue((new StatusCode(500))->isServerError());
        $this->assertFalse((new StatusCode(503))->isServerError());
    }
}
