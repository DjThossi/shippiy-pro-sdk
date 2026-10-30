<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_values;
use function count;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, Parcel>
 */
final readonly class ParcelCollection implements Countable, IteratorAggregate
{
    /**
     * @var Parcel[]
     */
    private array $parcels;

    public function __construct(Parcel ...$parcels)
    {
        $this->parcels = array_values($parcels);
    }

    /**
     * @return Parcel[]
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
     * @return Generator<int, Parcel>
     */
    public function getIterator(): Generator
    {
        yield from $this->parcels;
    }
}
