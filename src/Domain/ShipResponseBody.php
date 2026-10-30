<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class ShipResponseBody
{
    /**
     * @param string[]                   $pdf
     * @param string[]                   $zpl
     * @param array<int, array<mixed>>   $additionalTrackingNumbers
     */
    public function __construct(
        public string $result,
        public ShipStatus $status,
        public LabelUrlCollection $labelUrls,
        public ?string $newOrderId = null,
        public ?string $trackingCarrier = null,
        public ?string $trackingNumber = null,
        public ?string $trackingExternalLink = null,
        public ?string $deliveryCarrier = null,
        public ?string $errorMessage = null,
        public ?string $returnErrorMessage = null,
        public ?ErrorType $errorType = null,
        public ?ErrorSuggestion $errorSuggestion = null,
        public array $pdf = [],
        public array $zpl = [],
        public array $additionalTrackingNumbers = [],
        public ?ShipParcelCollection $parcels = null,
        public ?ShipParcelCollection $returnParcels = null,
    ) {
    }

    public function hasBusinessError(): bool
    {
        return $this->status === ShipStatus::Error || $this->errorMessage !== null;
    }
}
