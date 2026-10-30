<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\ParcelAttributes;
use PHPUnit\Framework\TestCase;

final class ParcelAttributesTest extends TestCase
{
    public function testEmptyByDefault(): void
    {
        $attributes = new ParcelAttributes();

        $this->assertTrue($attributes->isEmpty());
        $this->assertNull($attributes->parcelId);
        $this->assertNull($attributes->parcelType);
        $this->assertNull($attributes->spIoss);
    }

    public function testHoldsSpIoss(): void
    {
        $attributes = new ParcelAttributes(null, null, 'IOSS123456');

        $this->assertFalse($attributes->isEmpty());
        $this->assertSame('IOSS123456', $attributes->spIoss);
    }

    public function testHoldsParcelIdAndType(): void
    {
        $attributes = new ParcelAttributes('123456', 'Box');

        $this->assertFalse($attributes->isEmpty());
        $this->assertSame('123456', $attributes->parcelId);
        $this->assertSame('Box', $attributes->parcelType);
    }
}
