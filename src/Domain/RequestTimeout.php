<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidRequestTimeoutException;

final readonly class RequestTimeout
{
    public function __construct(
        public int $seconds,
    ) {
        if ($this->seconds <= 0) {
            throw new InvalidRequestTimeoutException('RequestTimeout must be a positive number of seconds');
        }
    }
}
