<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    public function testExposesAllFields(): void
    {
        $address = new Address(
            'John Doe',
            'ACME',
            '123 Main St',
            'Suite 4',
            'Park City',
            'UT',
            '84060',
            new CountryCode('US'),
            '5551231234',
            'john@example.com',
        );

        $this->assertSame('John Doe', $address->name);
        $this->assertSame('ACME', $address->company);
        $this->assertSame('123 Main St', $address->street1);
        $this->assertSame('Suite 4', $address->street2);
        $this->assertSame('Park City', $address->city);
        $this->assertSame('UT', $address->state);
        $this->assertSame('84060', $address->zip);
        $this->assertSame('US', $address->country->value);
        $this->assertSame('5551231234', $address->phone);
        $this->assertSame('john@example.com', $address->email);
    }
}
