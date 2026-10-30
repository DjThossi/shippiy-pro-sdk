<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class ShipParcel
{
    public function __construct(
        public ?string $trackingNumber = null,
        public ?string $pdf = null,
        public ?string $zpl = null,
        public ?ParcelAttributes $attributes = null,
    ) {
    }
}
