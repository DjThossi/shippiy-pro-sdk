<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\CashOnDeliveryType;
use DjThossi\ShippyProSdk\Domain\CustomInfoContentsType;
use DjThossi\ShippyProSdk\Domain\DimensionUnit;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\LabelType;
use DjThossi\ShippyProSdk\Domain\NonDeliveryOption;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use DjThossi\ShippyProSdk\Domain\WeightUnit;
use PHPUnit\Framework\TestCase;

final class ShipEnumsTest extends TestCase
{
    public function testDimensionUnitValues(): void
    {
        $this->assertSame('CM', DimensionUnit::Cm->value);
        $this->assertSame('IN', DimensionUnit::In->value);
        $this->assertSame(DimensionUnit::Cm, DimensionUnit::from('CM'));
        $this->assertNull(DimensionUnit::tryFrom('MM'));
    }

    public function testWeightUnitValues(): void
    {
        $this->assertSame('KG', WeightUnit::Kg->value);
        $this->assertSame('LB', WeightUnit::Lb->value);
        $this->assertSame('OZ', WeightUnit::Oz->value);
        $this->assertNull(WeightUnit::tryFrom('G'));
    }

    public function testCashOnDeliveryTypeValues(): void
    {
        $this->assertSame(0, CashOnDeliveryType::Cash->value);
        $this->assertSame(1, CashOnDeliveryType::CashiersCheck->value);
        $this->assertSame(2, CashOnDeliveryType::Check->value);
        $this->assertSame(3, CashOnDeliveryType::All->value);
        $this->assertNull(CashOnDeliveryType::tryFrom(9));
    }

    public function testIncotermValues(): void
    {
        $this->assertSame('DAP', Incoterm::Dap->value);
        $this->assertSame('DDP', Incoterm::Ddp->value);
        $this->assertSame('EXW', Incoterm::Exw->value);
        $this->assertNull(Incoterm::tryFrom('FOB'));
    }

    public function testCustomInfoContentsTypeValues(): void
    {
        $this->assertSame('documents', CustomInfoContentsType::Documents->value);
        $this->assertSame('gift', CustomInfoContentsType::Gift->value);
        $this->assertSame('merchandise', CustomInfoContentsType::Merchandise->value);
        $this->assertSame('returned_goods', CustomInfoContentsType::ReturnedGoods->value);
        $this->assertSame('sample', CustomInfoContentsType::Sample->value);
        $this->assertSame('other', CustomInfoContentsType::Other->value);
        $this->assertNull(CustomInfoContentsType::tryFrom('unknown'));
    }

    public function testNonDeliveryOptionValues(): void
    {
        $this->assertSame('return', NonDeliveryOption::Return->value);
        $this->assertSame('abandon', NonDeliveryOption::Abandon->value);
        $this->assertNull(NonDeliveryOption::tryFrom('destroy'));
    }

    public function testLabelTypeValues(): void
    {
        $this->assertSame('PDF', LabelType::Pdf->value);
        $this->assertSame('ZPL', LabelType::Zpl->value);
        $this->assertNull(LabelType::tryFrom('PNG'));
    }

    public function testShipStatusValues(): void
    {
        $this->assertSame('1', ShipStatus::Ok->value);
        $this->assertSame('2', ShipStatus::Error->value);
        $this->assertNull(ShipStatus::tryFrom('3'));
    }
}
