<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\Cn22Item;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use PHPUnit\Framework\TestCase;

final class Cn22ItemTest extends TestCase
{
    public function testMinimalItem(): void
    {
        $item = new Cn22Item('test', 2.0, 5, 10.5, new CountryCode('IT'), 'USD');

        $this->assertSame('test', $item->description);
        $this->assertSame(2.0, $item->weight);
        $this->assertSame(5, $item->quantity);
        $this->assertSame(10.5, $item->unitValue);
        $this->assertSame('IT', $item->originCountry->value);
        $this->assertSame('USD', $item->currency);
        $this->assertNull($item->hsCode);
        $this->assertNull($item->ean);
        $this->assertNull($item->sku);
        $this->assertNull($item->mpn);
        $this->assertNull($item->voc);
    }

    public function testFullItem(): void
    {
        $item = new Cn22Item(
            'shirt',
            0.3,
            2,
            19.99,
            new CountryCode('DE'),
            'EUR',
            '123456',
            '4006381333931',
            'TSHIRT-RED-M',
            'TS-1000-M',
            12.0,
        );

        $this->assertSame('123456', $item->hsCode);
        $this->assertSame('4006381333931', $item->ean);
        $this->assertSame('TSHIRT-RED-M', $item->sku);
        $this->assertSame('TS-1000-M', $item->mpn);
        $this->assertSame(12.0, $item->voc);
    }
}
