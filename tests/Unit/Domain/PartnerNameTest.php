<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Exception\InvalidPartnerNameException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PartnerNameTest extends TestCase
{
    public function testValidPartnerNameStoresValue(): void
    {
        $partnerName = new PartnerName('my-platform');

        $this->assertSame('my-platform', $partnerName->value);
    }

    public function testValueIsLowercased(): void
    {
        $partnerName = new PartnerName('ACME-WMS');

        $this->assertSame('acme-wms', $partnerName->value);
    }

    public function testSurroundingWhitespaceIsTrimmed(): void
    {
        $partnerName = new PartnerName('  My-Platform  ');

        $this->assertSame('my-platform', $partnerName->value);
    }

    #[DataProvider('emptyValueProvider')]
    public function testEmptyOrWhitespaceValueThrowsException(string $value): void
    {
        $this->expectException(InvalidPartnerNameException::class);
        $this->expectExceptionMessage('PartnerName must not be empty');

        new PartnerName($value);
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
        ];
    }
}
