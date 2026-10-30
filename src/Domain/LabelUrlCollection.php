<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_values;
use function count;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, string>
 */
final readonly class LabelUrlCollection implements Countable, IteratorAggregate
{
    /**
     * @var string[]
     */
    private array $urls;

    public function __construct(string ...$urls)
    {
        $this->urls = array_values($urls);
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->urls;
    }

    public function isEmpty(): bool
    {
        return $this->urls === [];
    }

    public function count(): int
    {
        return count($this->urls);
    }

    /**
     * @return Generator<int, string>
     */
    public function getIterator(): Generator
    {
        yield from $this->urls;
    }
}
