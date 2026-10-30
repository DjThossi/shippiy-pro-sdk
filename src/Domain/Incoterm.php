<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

enum Incoterm: string
{
    case Dap = 'DAP';
    case Ddp = 'DDP';
    case Exw = 'EXW';
}
