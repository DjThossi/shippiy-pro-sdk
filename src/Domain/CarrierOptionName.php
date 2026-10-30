<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

/**
 * Documented named helpers for common DHL and UPS carrier options. This is a
 * convenience subset only — it is NOT exhaustive. The authoritative set of
 * valid option names per carrier is returned by the GetCarrierOptions method at
 * runtime, and a CarrierOption may be constructed with any name.
 */
final class CarrierOptionName
{
    // DHL
    public const string DHL_SATURDAY_DELIVERY = 'mydhlsaturday_delivery';

    // UPS
    public const string UPS_DANGEROUS_GOODS = 'upsdangerousgoods';

    private function __construct()
    {
    }
}
