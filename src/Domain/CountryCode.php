<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidCountryCodeException;

use function preg_match;
use function strtoupper;
use function trim;

final readonly class CountryCode
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (preg_match('/^[A-Z]{2}$/', $normalized) !== 1) {
            throw new InvalidCountryCodeException('CountryCode must be a two-letter ISO 3166-1 alpha-2 code');
        }

        $this->value = $normalized;
    }
}
