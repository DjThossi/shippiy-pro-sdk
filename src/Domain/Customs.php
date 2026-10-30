<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class Customs
{
    public function __construct(
        public Incoterm $incoterm = Incoterm::Dap,
        public ?string $billAccountNumber = null,
        public ?CustomInfoContentsType $contentsType = null,
        public ?Cn22ItemCollection $items = null,
        public ?NonDeliveryOption $nonDeliveryOption = null,
    ) {
    }
}
