<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use Countable;
use DjThossi\ShippyProSdk\Domain\LabelUrlCollection;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

final class LabelUrlCollectionTest extends TestCase
{
    public function testEmpty(): void
    {
        $collection = new LabelUrlCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame(0, $collection->count());
        $this->assertSame([], $collection->all());
    }

    public function testHoldsUrlsInOrder(): void
    {
        $collection = new LabelUrlCollection('a', 'b');

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(2, $collection->count());
        $this->assertSame(['a', 'b'], $collection->all());
    }

    public function testImplementsCountableAndIteratorAggregate(): void
    {
        $collection = new LabelUrlCollection('a');

        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testIsIterableInOrder(): void
    {
        $collection = new LabelUrlCollection('a', 'b', 'c');

        $seen = [];
        foreach ($collection as $url) {
            $seen[] = $url;
        }

        $this->assertSame(['a', 'b', 'c'], $seen);
        $this->assertSame(['a', 'b', 'c'], iterator_to_array($collection->getIterator()));
    }
}
