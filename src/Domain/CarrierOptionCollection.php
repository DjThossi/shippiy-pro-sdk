<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_values;
use function count;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, CarrierOption>
 */
final readonly class CarrierOptionCollection implements Countable, IteratorAggregate
{
    /**
     * @var CarrierOption[]
     */
    private array $options;

    public function __construct(CarrierOption ...$options)
    {
        $this->options = array_values($options);
    }

    /**
     * @return CarrierOption[]
     */
    public function all(): array
    {
        return $this->options;
    }

    public function isEmpty(): bool
    {
        return $this->options === [];
    }

    public function count(): int
    {
        return count($this->options);
    }

    /**
     * @return Generator<int, CarrierOption>
     */
    public function getIterator(): Generator
    {
        yield from $this->options;
    }
}
