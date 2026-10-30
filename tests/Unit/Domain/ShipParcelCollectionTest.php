<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use Countable;
use DjThossi\ShippyProSdk\Domain\ShipParcel;
use DjThossi\ShippyProSdk\Domain\ShipParcelCollection;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

final class ShipParcelCollectionTest extends TestCase
{
    public function testEmpty(): void
    {
        $collection = new ShipParcelCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame(0, $collection->count());
        $this->assertSame([], $collection->all());
    }

    public function testHoldsParcels(): void
    {
        $a = new ShipParcel('TN1', 'pdf', 'zpl');
        $b = new ShipParcel('TN2');
        $collection = new ShipParcelCollection($a, $b);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(2, $collection->count());
        $this->assertSame([$a, $b], $collection->all());
        $this->assertSame('TN1', $collection->all()[0]->trackingNumber);
    }

    public function testImplementsCountableAndIteratorAggregate(): void
    {
        $collection = new ShipParcelCollection(new ShipParcel('TN1'));

        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testIsIterableInOrder(): void
    {
        $a = new ShipParcel('TN1');
        $b = new ShipParcel('TN2');
        $collection = new ShipParcelCollection($a, $b);

        $seen = [];
        foreach ($collection as $parcel) {
            $seen[] = $parcel;
        }

        $this->assertSame([$a, $b], $seen);
        $this->assertSame([$a, $b], iterator_to_array($collection->getIterator()));
    }
}
