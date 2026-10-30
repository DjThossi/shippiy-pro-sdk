<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_values;
use function count;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, ShipParcel>
 */
final readonly class ShipParcelCollection implements Countable, IteratorAggregate
{
    /**
     * @var ShipParcel[]
     */
    private array $parcels;

    public function __construct(ShipParcel ...$parcels)
    {
        $this->parcels = array_values($parcels);
    }

    /**
     * @return ShipParcel[]
     */
    public function all(): array
    {
        return $this->parcels;
    }

    public function isEmpty(): bool
    {
        return $this->parcels === [];
    }

    public function count(): int
    {
        return count($this->parcels);
    }

    /**
     * @return Generator<int, ShipParcel>
     */
    public function getIterator(): Generator
    {
        yield from $this->parcels;
    }
}
