<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use Countable;
use DjThossi\ShippyProSdk\Domain\Cn22Item;
use DjThossi\ShippyProSdk\Domain\Cn22ItemCollection;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

final class Cn22ItemCollectionTest extends TestCase
{
    public function testEmpty(): void
    {
        $collection = new Cn22ItemCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame(0, $collection->count());
        $this->assertSame([], $collection->all());
    }

    public function testHoldsItems(): void
    {
        $item = new Cn22Item('x', 1.0, 1, 1.0, new CountryCode('IT'), 'EUR');
        $collection = new Cn22ItemCollection($item);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(1, $collection->count());
        $this->assertSame([$item], $collection->all());
    }

    public function testImplementsCountableAndIteratorAggregate(): void
    {
        $collection = new Cn22ItemCollection(new Cn22Item('x', 1.0, 1, 1.0, new CountryCode('IT'), 'EUR'));

        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testIsIterableInOrder(): void
    {
        $a = new Cn22Item('a', 1.0, 1, 1.0, new CountryCode('IT'), 'EUR');
        $b = new Cn22Item('b', 2.0, 2, 2.0, new CountryCode('DE'), 'EUR');
        $collection = new Cn22ItemCollection($a, $b);

        $seen = [];
        foreach ($collection as $item) {
            $seen[] = $item;
        }

        $this->assertSame([$a, $b], $seen);
        $this->assertSame([$a, $b], iterator_to_array($collection->getIterator()));
    }
}
