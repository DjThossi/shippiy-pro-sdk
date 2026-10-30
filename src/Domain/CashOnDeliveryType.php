<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum CashOnDeliveryType: int
{
    case Cash = 0;
    case CashiersCheck = 1;
    case Check = 2;
    case All = 3;
}
