<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\Insurance;
use PHPUnit\Framework\TestCase;

final class InsuranceTest extends TestCase
{
    public function testDefaultsToEurWhenNoCurrencyGiven(): void
    {
        $insurance = new Insurance(10.0);

        $this->assertSame(10.0, $insurance->amount);
        $this->assertSame('EUR', $insurance->currency);
    }

    public function testUsesProvidedCurrencyUpperCased(): void
    {
        $insurance = new Insurance(25.0, 'usd');

        $this->assertSame(25.0, $insurance->amount);
        $this->assertSame('USD', $insurance->currency);
    }
}
