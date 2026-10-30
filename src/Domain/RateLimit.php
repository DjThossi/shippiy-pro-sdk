<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class RateLimit
{
    public function __construct(
        public ?int $limit,
        public ?int $remaining,
        public ?int $reset,
    ) {
    }

    public function hasData(): bool
    {
        return $this->limit !== null
            || $this->remaining !== null
            || $this->reset !== null;
    }
}
