<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class StatusResponseBody
{
    public function __construct(
        public string $status,
        public string $version,
    ) {
    }
}
