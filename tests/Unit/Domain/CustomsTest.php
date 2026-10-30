<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\Cn22ItemCollection;
use DjThossi\ShippyProSdk\Domain\CustomInfoContentsType;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\NonDeliveryOption;
use PHPUnit\Framework\TestCase;

final class CustomsTest extends TestCase
{
    public function testDefaultsToDapWithNoOtherData(): void
    {
        $customs = new Customs();

        $this->assertSame(Incoterm::Dap, $customs->incoterm);
        $this->assertNull($customs->billAccountNumber);
        $this->assertNull($customs->contentsType);
        $this->assertNull($customs->items);
        $this->assertNull($customs->nonDeliveryOption);
    }

    public function testStoresAllValues(): void
    {
        $items = new Cn22ItemCollection();
        $customs = new Customs(
            Incoterm::Exw,
            'ACC-123',
            CustomInfoContentsType::Merchandise,
            $items,
            NonDeliveryOption::Return,
        );

        $this->assertSame(Incoterm::Exw, $customs->incoterm);
        $this->assertSame('ACC-123', $customs->billAccountNumber);
        $this->assertSame(CustomInfoContentsType::Merchandise, $customs->contentsType);
        $this->assertSame($items, $customs->items);
        $this->assertSame(NonDeliveryOption::Return, $customs->nonDeliveryOption);
    }
}
