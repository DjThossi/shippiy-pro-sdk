<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class Parcel
{
    public function __construct(
        public float $length,
        public float $width,
        public float $height,
        public float $weight,
        public DimensionUnit $dimensionUnit = DimensionUnit::Cm,
        public WeightUnit $weightUnit = WeightUnit::Kg,
        public ?int $package = null,
        public ?ParcelAttributes $attributes = null,
    ) {
    }
}
