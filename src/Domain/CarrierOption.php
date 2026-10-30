<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

/**
 * A single carrier-specific option name/value pair, mapping to one entry of the
 * v1 `CarrierOptions[]` array. Any option name is accepted: the authoritative
 * set of valid names per carrier is returned by the GetCarrierOptions method at
 * runtime. See CarrierOptionName for documented helper constants.
 */
final readonly class CarrierOption
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
