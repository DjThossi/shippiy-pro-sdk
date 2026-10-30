<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\MoneyAmount;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Exception\InvalidCustomsException;
use PHPUnit\Framework\TestCase;

final class ShipRequestTest extends TestCase
{
    public function testDomesticRequestWithoutCustomsIsValid(): void
    {
        $request = $this->build('US', 'US');

        $this->assertFalse($request->isCrossBorder());
        $this->assertSame('ORDER1', $request->transactionId);
    }

    public function testCrossBorderIsDetected(): void
    {
        $request = $this->build('DE', 'US', totalValue: new MoneyAmount('100', 'EUR'));

        $this->assertTrue($request->isCrossBorder());
    }

    public function testCrossBorderWithoutTotalValueThrows(): void
    {
        $this->expectException(InvalidCustomsException::class);
        $this->expectExceptionMessage('A cross-border shipment requires a TotalValue');

        $this->build('DE', 'US');
    }

    public function testExwWithoutBillAccountNumberThrows(): void
    {
        $this->expectException(InvalidCustomsException::class);
        $this->expectExceptionMessage('Incoterm EXW requires a BillAccountNumber');

        $this->build('US', 'US', customs: new Customs(Incoterm::Exw));
    }

    public function testExwWithBillAccountNumberIsValid(): void
    {
        $request = $this->build('US', 'US', customs: new Customs(Incoterm::Exw, 'ACC-1'));

        $this->assertSame(Incoterm::Exw, $request->customs?->incoterm);
    }

    public function testBillAccountNumberWithoutExwThrows(): void
    {
        $this->expectException(InvalidCustomsException::class);
        $this->expectExceptionMessage('BillAccountNumber may only be set when the Incoterm is EXW');

        $this->build('US', 'US', customs: new Customs(Incoterm::Dap, 'ACC-1'));
    }

    public function testDapCustomsWithoutBillAccountIsValid(): void
    {
        $request = $this->build('US', 'US', customs: new Customs(Incoterm::Dap));

        $this->assertSame(Incoterm::Dap, $request->customs?->incoterm);
    }

    private function build(
        string $fromCountry,
        string $toCountry,
        ?MoneyAmount $totalValue = null,
        ?Customs $customs = null,
    ): ShipRequest {
        $from = new Address('A', '', 'S1', '', 'City', 'ST', '1', new CountryCode($fromCountry), '1', 'a@b.c');
        $to = new Address('B', '', 'S1', '', 'City', 'ST', '1', new CountryCode($toCountry), '1', 'b@b.c');

        return new ShipRequest(
            $to,
            $from,
            new ParcelCollection(new Parcel(1.0, 1.0, 1.0, 1.0)),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
            totalValue: $totalValue,
            customs: $customs,
        );
    }
}
