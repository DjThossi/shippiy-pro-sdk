<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class ErrorSuggestion
{
    public function __construct(
        public ?string $errorDescription = null,
        public ?string $solution = null,
    ) {
    }
}
