<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

/**
 * A unified error container across API versions.
 *
 * v1 reports errors in the body as `{"Error": ...}` or
 * `{"Result": "NOK", "ErrorMessage": ...}`; v2 follows RFC 9457 with a
 * `ProblemDetail` body (`type`, `title`, `status`, `detail`, `instance` and
 * optional field-level `errors`). Both are normalized into this object so a
 * caller gets at the error the same way regardless of the endpoint version.
 */
final readonly class ApiError
{
    /**
     * @param FieldError[] $fieldErrors
     */
    public function __construct(
        public string $detail,
        public ?string $type = null,
        public ?string $title = null,
        public ?int $status = null,
        public ?string $instance = null,
        public array $fieldErrors = [],
    ) {
    }

    public function hasFieldErrors(): bool
    {
        return $this->fieldErrors !== [];
    }
}
