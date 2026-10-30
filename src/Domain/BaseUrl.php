<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidBaseUrlException;

use const FILTER_VALIDATE_URL;

use function filter_var;
use function rtrim;

final readonly class BaseUrl
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = rtrim($value, '/');
        if ($normalized === '') {
            throw new InvalidBaseUrlException('BaseUrl must not be empty');
        }

        if (!filter_var($normalized, FILTER_VALIDATE_URL)) {
            throw new InvalidBaseUrlException('BaseUrl must be a valid URL');
        }

        $this->value = $normalized;
    }
}
