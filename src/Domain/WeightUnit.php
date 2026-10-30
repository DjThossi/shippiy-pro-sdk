<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum WeightUnit: string
{
    case Kg = 'KG';
    case Lb = 'LB';
    case Oz = 'OZ';
}
