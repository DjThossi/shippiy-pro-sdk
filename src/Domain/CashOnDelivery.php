<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function strtoupper;

final readonly class CashOnDelivery
{
    public const string DEFAULT_CURRENCY = 'EUR';

    public float $amount;
    public string $currency;
    public CashOnDeliveryType $type;

    public function __construct(
        float $amount,
        ?string $currency = null,
        CashOnDeliveryType $type = CashOnDeliveryType::Cash,
    ) {
        $this->amount = $amount;
        $this->currency = $currency !== null ? strtoupper($currency) : self::DEFAULT_CURRENCY;
        $this->type = $type;
    }
}
