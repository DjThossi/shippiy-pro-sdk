<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class FieldError
{
    public function __construct(
        public string $detail,
        public ?string $pointer = null,
        public ?string $parameter = null,
        public ?string $code = null,
    ) {
    }
}
