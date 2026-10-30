<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use Countable;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

final class ParcelCollectionTest extends TestCase
{
    public function testEmptyCollection(): void
    {
        $collection = new ParcelCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame(0, $collection->count());
        $this->assertSame([], $collection->all());
    }

    public function testHoldsParcels(): void
    {
        $a = new Parcel(1.0, 1.0, 1.0, 1.0);
        $b = new Parcel(2.0, 2.0, 2.0, 2.0);
        $collection = new ParcelCollection($a, $b);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(2, $collection->count());
        $this->assertSame([$a, $b], $collection->all());
    }

    public function testImplementsCountableAndIteratorAggregate(): void
    {
        $collection = new ParcelCollection(new Parcel(1.0, 1.0, 1.0, 1.0));

        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testIsIterableInOrder(): void
    {
        $a = new Parcel(1.0, 1.0, 1.0, 1.0);
        $b = new Parcel(2.0, 2.0, 2.0, 2.0);
        $collection = new ParcelCollection($a, $b);

        $seen = [];
        foreach ($collection as $parcel) {
            $seen[] = $parcel;
        }

        $this->assertSame([$a, $b], $seen);
        $this->assertSame([$a, $b], iterator_to_array($collection->getIterator()));
    }
}
