<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\CashOnDelivery;
use DjThossi\ShippyProSdk\Domain\CashOnDeliveryType;
use PHPUnit\Framework\TestCase;

final class CashOnDeliveryTest extends TestCase
{
    public function testDefaults(): void
    {
        $cod = new CashOnDelivery(50.0);

        $this->assertSame(50.0, $cod->amount);
        $this->assertSame('EUR', $cod->currency);
        $this->assertSame(CashOnDeliveryType::Cash, $cod->type);
    }

    public function testProvidedValues(): void
    {
        $cod = new CashOnDelivery(75.0, 'gbp', CashOnDeliveryType::Check);

        $this->assertSame(75.0, $cod->amount);
        $this->assertSame('GBP', $cod->currency);
        $this->assertSame(CashOnDeliveryType::Check, $cod->type);
    }
}
