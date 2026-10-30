<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidCustomsException;

/**
 * Typed model of the v1 `ShipParams` request object.
 *
 * Only the clearly-documented customs invariants are enforced client-side at
 * construction time (Incoterm EXW requires a BillAccountNumber and vice versa;
 * a cross-border shipment requires a TotalValue). Every other carrier- and
 * country-specific rule is left to the API.
 */
final readonly class ShipRequest
{
    public function __construct(
        public Address $toAddress,
        public Address $fromAddress,
        public ParcelCollection $parcels,
        public string $transactionId,
        public string $contentDescription,
        public string $carrierName,
        public string $carrierService,
        public int $carrierId,
        public ?string $rateId = null,
        public ?string $orderId = null,
        public ?MoneyAmount $totalValue = null,
        public ?Insurance $insurance = null,
        public ?CashOnDelivery $cashOnDelivery = null,
        public ?string $note = null,
        public ?string $carrierNote = null,
        public ?string $userNote = null,
        public ?string $paymentMethod = null,
        public ?Customs $customs = null,
        public ?CarrierOptionCollection $carrierOptions = null,
        public ?bool $isReturn = null,
        public ?string $returnCarrierName = null,
        public ?string $returnCarrierService = null,
        public ?int $returnCarrierId = null,
        public ?Insurance $returnInsurance = null,
        public ?CarrierOptionCollection $returnCarrierOptions = null,
        public ?Address $returnTo = null,
        public ?LabelFormatting $labelFormatting = null,
        public ?ParcelAttributes $attributes = null,
    ) {
        $this->assertIncotermBillAccountConsistency();
        $this->assertCrossBorderHasTotalValue();
    }

    public function isCrossBorder(): bool
    {
        return $this->fromAddress->country->value !== $this->toAddress->country->value;
    }

    private function assertIncotermBillAccountConsistency(): void
    {
        if ($this->customs === null) {
            return;
        }

        $isExw = $this->customs->incoterm === Incoterm::Exw;
        $hasBillAccount = $this->customs->billAccountNumber !== null
            && $this->customs->billAccountNumber !== '';

        if ($isExw && !$hasBillAccount) {
            throw new InvalidCustomsException('Incoterm EXW requires a BillAccountNumber');
        }

        if (!$isExw && $hasBillAccount) {
            throw new InvalidCustomsException('BillAccountNumber may only be set when the Incoterm is EXW');
        }
    }

    private function assertCrossBorderHasTotalValue(): void
    {
        if ($this->isCrossBorder() && $this->totalValue === null) {
            throw new InvalidCustomsException('A cross-border shipment requires a TotalValue');
        }
    }
}
