<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Exception\InvalidBaseUrlException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BaseUrlTest extends TestCase
{
    public function testValidBaseUrlStoresValue(): void
    {
        $baseUrl = new BaseUrl('https://api.shippypro.com');

        $this->assertSame('https://api.shippypro.com', $baseUrl->value);
    }

    public function testTrailingSlashIsTrimmed(): void
    {
        $baseUrl = new BaseUrl('https://api.shippypro.com/');

        $this->assertSame('https://api.shippypro.com', $baseUrl->value);
    }

    public function testMultipleTrailingSlashesAreTrimmed(): void
    {
        $baseUrl = new BaseUrl('https://api.shippypro.com///');

        $this->assertSame('https://api.shippypro.com', $baseUrl->value);
    }

    public function testEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidBaseUrlException::class);
        $this->expectExceptionMessage('BaseUrl must not be empty');

        new BaseUrl('');
    }

    public function testSlashOnlyValueThrowsEmptyException(): void
    {
        $this->expectException(InvalidBaseUrlException::class);
        $this->expectExceptionMessage('BaseUrl must not be empty');

        new BaseUrl('/');
    }

    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrlThrowsException(string $value): void
    {
        $this->expectException(InvalidBaseUrlException::class);
        $this->expectExceptionMessage('BaseUrl must be a valid URL');

        new BaseUrl($value);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidUrlProvider(): array
    {
        return [
            'plain text' => ['not-a-url'],
            'missing scheme' => ['api.shippypro.com'],
            'spaces' => ['http:// invalid'],
        ];
    }
}
