<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidApiKeyException;

use function trim;

final readonly class ApiKey
{
    public string $value;

    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new InvalidApiKeyException('ApiKey must not be empty');
        }

        $this->value = $value;
    }
}
