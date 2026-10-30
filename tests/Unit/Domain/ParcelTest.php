<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\DimensionUnit;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelAttributes;
use DjThossi\ShippyProSdk\Domain\WeightUnit;
use PHPUnit\Framework\TestCase;

final class ParcelTest extends TestCase
{
    public function testDefaultsAreCmAndKg(): void
    {
        $parcel = new Parcel(5.0, 5.0, 5.0, 10.0);

        $this->assertSame(DimensionUnit::Cm, $parcel->dimensionUnit);
        $this->assertSame(WeightUnit::Kg, $parcel->weightUnit);
        $this->assertNull($parcel->package);
        $this->assertNull($parcel->attributes);
    }

    public function testStoresAllValues(): void
    {
        $attributes = new ParcelAttributes('p1', 'Box');
        $parcel = new Parcel(1.0, 2.0, 3.0, 4.0, DimensionUnit::In, WeightUnit::Lb, 7, $attributes);

        $this->assertSame(1.0, $parcel->length);
        $this->assertSame(2.0, $parcel->width);
        $this->assertSame(3.0, $parcel->height);
        $this->assertSame(4.0, $parcel->weight);
        $this->assertSame(DimensionUnit::In, $parcel->dimensionUnit);
        $this->assertSame(WeightUnit::Lb, $parcel->weightUnit);
        $this->assertSame(7, $parcel->package);
        $this->assertSame($attributes, $parcel->attributes);
    }
}
