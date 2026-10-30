<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Exception;

use function implode;

use InvalidArgumentException;

use function sprintf;

final class MissingRequiredFieldsException extends InvalidArgumentException
{
    /**
     * @param string[] $missingFields
     */
    public function __construct(array $missingFields)
    {
        parent::__construct(sprintf(
            'Missing required fields: %s',
            implode(', ', $missingFields)
        ));
    }
}
