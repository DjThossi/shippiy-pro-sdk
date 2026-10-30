<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidPartnerNameException;

use function strtolower;
use function trim;

final readonly class PartnerName
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            throw new InvalidPartnerNameException('PartnerName must not be empty');
        }

        $this->value = $normalized;
    }
}
