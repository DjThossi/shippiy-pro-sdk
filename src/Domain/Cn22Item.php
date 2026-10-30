<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

/**
 * A single customs (CN22) line item, mapping to one entry of the v1 `CN22Info[]`
 * array. There is no separate CN23 type: this same container carries the line
 * items for both low-value (CN22) and higher-value (CN23/CP71) cross-border
 * shipments; ShippyPro and the carrier determine the physical customs form from
 * the shipment value, destination and weight.
 *
 * `sku` is required for EU B2C shipments valued at or below 150 EUR as of
 * 2026-11-01, per the ShippyPro v1 documentation.
 */
final readonly class Cn22Item
{
    public function __construct(
        public string $description,
        public float $weight,
        public int $quantity,
        public float $unitValue,
        public CountryCode $originCountry,
        public string $currency,
        public ?string $hsCode = null,
        public ?string $ean = null,
        public ?string $sku = null,
        public ?string $mpn = null,
        public ?float $voc = null,
    ) {
    }
}
