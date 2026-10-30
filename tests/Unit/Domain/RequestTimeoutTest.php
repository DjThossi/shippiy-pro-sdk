<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\RequestTimeout;
use DjThossi\ShippyProSdk\Exception\InvalidRequestTimeoutException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTimeoutTest extends TestCase
{
    public function testValidPositiveTimeoutStoresValue(): void
    {
        $timeout = new RequestTimeout(10);

        $this->assertSame(10, $timeout->seconds);
    }

    public function testMinimalPositiveTimeoutIsAccepted(): void
    {
        $timeout = new RequestTimeout(1);

        $this->assertSame(1, $timeout->seconds);
    }

    #[DataProvider('nonPositiveProvider')]
    public function testNonPositiveTimeoutThrowsException(int $seconds): void
    {
        $this->expectException(InvalidRequestTimeoutException::class);
        $this->expectExceptionMessage('RequestTimeout must be a positive number of seconds');

        new RequestTimeout($seconds);
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function nonPositiveProvider(): array
    {
        return [
            'zero' => [0],
            'minus one' => [-1],
            'large negative' => [-3600],
        ];
    }
}
