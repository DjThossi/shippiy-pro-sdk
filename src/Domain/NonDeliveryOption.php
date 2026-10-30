<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum NonDeliveryOption: string
{
    case Return = 'return';
    case Abandon = 'abandon';
}
