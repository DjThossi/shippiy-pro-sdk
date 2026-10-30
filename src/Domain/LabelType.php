<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum LabelType: string
{
    case Pdf = 'PDF';
    case Zpl = 'ZPL';
}
