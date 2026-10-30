<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Exception\InvalidApiKeyException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiKeyTest extends TestCase
{
    public function testValidApiKeyStoresValue(): void
    {
        $apiKey = new ApiKey('my-secret-key');

        $this->assertSame('my-secret-key', $apiKey->value);
    }

    public function testValueIsPreservedVerbatim(): void
    {
        $apiKey = new ApiKey('  spaced-key  ');

        $this->assertSame('  spaced-key  ', $apiKey->value);
    }

    #[DataProvider('emptyValueProvider')]
    public function testEmptyOrWhitespaceValueThrowsException(string $value): void
    {
        $this->expectException(InvalidApiKeyException::class);
        $this->expectExceptionMessage('ApiKey must not be empty');

        new ApiKey($value);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function emptyValueProvider(): array
    {
        return [
            'empty string' => [''],
            'single space' => [' '],
            'multiple spaces' => ['   '],
            'tab and newline' => ["\t\n"],
        ];
    }
}
