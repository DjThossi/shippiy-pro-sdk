<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_values;
use function count;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, Cn22Item>
 */
final readonly class Cn22ItemCollection implements Countable, IteratorAggregate
{
    /**
     * @var Cn22Item[]
     */
    private array $items;

    public function __construct(Cn22Item ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return Cn22Item[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Generator<int, Cn22Item>
     */
    public function getIterator(): Generator
    {
        yield from $this->items;
    }
}
