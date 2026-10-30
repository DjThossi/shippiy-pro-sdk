<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class ErrorType
{
    public function __construct(
        public ?string $code = null,
        public ?string $name = null,
        public ?string $typeDescription = null,
    ) {
    }
}
