<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use Countable;
use DjThossi\ShippyProSdk\Domain\CarrierOption;
use DjThossi\ShippyProSdk\Domain\CarrierOptionCollection;
use DjThossi\ShippyProSdk\Domain\CarrierOptionName;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CarrierOptionTest extends TestCase
{
    public function testHoldsNameAndValue(): void
    {
        $option = new CarrierOption('mydhlsaturday_delivery', '1');

        $this->assertSame('mydhlsaturday_delivery', $option->name);
        $this->assertSame('1', $option->value);
    }

    public function testAcceptsArbitraryName(): void
    {
        $option = new CarrierOption('some_future_option_name', 'x');

        $this->assertSame('some_future_option_name', $option->name);
    }

    public function testNamedHelperConstants(): void
    {
        $this->assertSame('mydhlsaturday_delivery', CarrierOptionName::DHL_SATURDAY_DELIVERY);
        $this->assertSame('upsdangerousgoods', CarrierOptionName::UPS_DANGEROUS_GOODS);

        $option = new CarrierOption(CarrierOptionName::UPS_DANGEROUS_GOODS, '1');
        $this->assertSame('upsdangerousgoods', $option->name);
    }

    public function testCollection(): void
    {
        $a = new CarrierOption('a', '1');
        $b = new CarrierOption('b', '2');
        $collection = new CarrierOptionCollection($a, $b);

        $this->assertSame([$a, $b], $collection->all());
        $this->assertSame(2, $collection->count());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue((new CarrierOptionCollection())->isEmpty());
    }

    public function testCarrierOptionNameCannotBeInstantiated(): void
    {
        $reflection = new ReflectionClass(CarrierOptionName::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());

        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($instance);

        $this->assertInstanceOf(CarrierOptionName::class, $instance);
    }

    public function testCollectionImplementsCountableAndIteratorAggregate(): void
    {
        $collection = new CarrierOptionCollection(new CarrierOption('a', '1'));

        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testCollectionIsIterableInOrder(): void
    {
        $a = new CarrierOption('a', '1');
        $b = new CarrierOption('b', '2');
        $collection = new CarrierOptionCollection($a, $b);

        $seen = [];
        foreach ($collection as $option) {
            $seen[] = $option;
        }

        $this->assertSame([$a, $b], $seen);
        $this->assertSame([$a, $b], iterator_to_array($collection->getIterator()));
    }
}
