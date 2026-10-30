<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class Address
{
    public function __construct(
        public string $name,
        public string $company,
        public string $street1,
        public string $street2,
        public string $city,
        public string $state,
        public string $zip,
        public CountryCode $country,
        public string $phone,
        public string $email,
    ) {
    }
}
