<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class ParcelAttributes
{
    public function __construct(
        public ?string $parcelId = null,
        public ?string $parcelType = null,
        public ?string $spIoss = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->parcelId === null
            && $this->parcelType === null
            && $this->spIoss === null;
    }
}
