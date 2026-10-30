<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Exception\InvalidCountryCodeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CountryCodeTest extends TestCase
{
    public function testValidCodeStoresUpperCaseValue(): void
    {
        $this->assertSame('DE', (new CountryCode('DE'))->value);
    }

    public function testLowerCaseIsNormalizedToUpperCase(): void
    {
        $this->assertSame('IT', (new CountryCode('it'))->value);
    }

    public function testSurroundingWhitespaceIsTrimmed(): void
    {
        $this->assertSame('US', (new CountryCode('  us  '))->value);
    }

    #[DataProvider('invalidProvider')]
    public function testInvalidCodeThrowsException(string $value): void
    {
        $this->expectException(InvalidCountryCodeException::class);
        $this->expectExceptionMessage('CountryCode must be a two-letter ISO 3166-1 alpha-2 code');

        new CountryCode($value);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty' => [''],
            'one letter' => ['D'],
            'three letters' => ['DEU'],
            'digits' => ['12'],
            'letter and digit' => ['D1'],
            'symbol' => ['D-'],
        ];
    }
}
