<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum CustomInfoContentsType: string
{
    case Documents = 'documents';
    case Gift = 'gift';
    case Merchandise = 'merchandise';
    case ReturnedGoods = 'returned_goods';
    case Sample = 'sample';
    case Other = 'other';
}
