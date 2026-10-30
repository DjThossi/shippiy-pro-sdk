<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Property;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CarrierOption;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\Insurance;
use DjThossi\ShippyProSdk\Domain\MoneyAmount;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Exception\InvalidCountryCodeException;
use DjThossi\ShippyProSdk\Exception\InvalidCustomsException;
use DjThossi\ShippyProSdk\Exception\InvalidMoneyAmountException;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function preg_match;

final class ShipValueObjectPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Feature: ship-endpoint, Property 3: For any string matching exactly two ASCII letters, constructing a CountryCode
     * SHALL succeed and store the value upper-cased; for any string that is not exactly two ASCII letters, construction
     * SHALL throw InvalidCountryCodeException.
     */
    #[ErisRepeat(100)]
    public function testCountryCodeValidationInvariant(): void
    {
        $this->forAll(Generators::string())
            ->then(function (string $value): void {
                $isTwoLetters = preg_match('/^[A-Za-z]{2}$/', $value) === 1;

                if ($isTwoLetters) {
                    $this->assertSame(strtoupper($value), (new CountryCode($value))->value);

                    return;
                }

                $this->expectException(InvalidCountryCodeException::class);
                new CountryCode($value);
            });
    }

    /**
     * Feature: ship-endpoint, Property 4: For any amount string that uses "." as its only decimal separator paired with
     * a non-empty currency, constructing a MoneyAmount SHALL succeed and toApiString() SHALL equal "amount currency";
     * for any amount string that uses "," as a decimal separator, construction SHALL throw InvalidMoneyAmountException.
     */
    #[ErisRepeat(100)]
    public function testMoneyAmountDecimalSeparatorInvariant(): void
    {
        $this->forAll(Generators::choose(0, 100000), Generators::choose(0, 99))
            ->then(function (int $whole, int $frac): void {
                $amount = $whole . '.' . $frac;
                $money = new MoneyAmount($amount, 'EUR');
                $this->assertSame($amount . ' EUR', $money->toApiString());

                $commaAmount = $whole . ',' . $frac;
                $this->expectException(InvalidMoneyAmountException::class);
                new MoneyAmount($commaAmount, 'EUR');
            });
    }

    /**
     * Feature: ship-endpoint, Property 6: For any non-empty option name and value, constructing a CarrierOption SHALL
     * succeed and preserve the name and value unchanged, regardless of whether the name is a documented helper constant.
     */
    #[ErisRepeat(100)]
    public function testCarrierOptionAcceptsAnyName(): void
    {
        $this->forAll(Generators::string(), Generators::string())
            ->then(function (string $name, string $value): void {
                $option = new CarrierOption($name, $value);

                $this->assertSame($name, $option->name);
                $this->assertSame($value, $option->value);
            });
    }

    /**
     * Feature: ship-endpoint, Property 8: For any ShipRequest whose customs Incoterm is EXW and that provides no
     * BillAccountNumber, construction SHALL throw InvalidCustomsException.
     */
    #[ErisRepeat(100)]
    public function testExwRequiresBillAccountNumber(): void
    {
        $this->forAll(Generators::elements('', ' ', '   '))
            ->then(function (string $billAccount): void {
                $this->expectException(InvalidCustomsException::class);
                $this->buildRequest('US', 'US', new Customs(Incoterm::Exw, $billAccount === '' ? null : $billAccount));
            });
    }

    /**
     * Feature: ship-endpoint, Property 9: For any ShipRequest that provides a BillAccountNumber while its customs
     * Incoterm is not EXW, construction SHALL throw InvalidCustomsException.
     */
    #[ErisRepeat(100)]
    public function testBillAccountNumberRequiresExw(): void
    {
        $this->forAll(Generators::elements(Incoterm::Dap, Incoterm::Ddp), Generators::string())
            ->then(function (Incoterm $incoterm, string $account): void {
                if ($account === '') {
                    $account = 'ACC';
                }

                $this->expectException(InvalidCustomsException::class);
                $this->buildRequest('US', 'US', new Customs($incoterm, $account));
            });
    }

    /**
     * Feature: ship-endpoint, Property 10: For any ShipRequest whose from_address country differs from its to_address
     * country and that provides no TotalValue, construction SHALL throw InvalidCustomsException; for a request with
     * matching countries or with a TotalValue present, this invariant SHALL NOT reject construction.
     */
    #[ErisRepeat(100)]
    public function testCrossBorderRequiresTotalValue(): void
    {
        $this->forAll(
            Generators::elements('US', 'DE', 'IT', 'FR'),
            Generators::elements('US', 'DE', 'IT', 'FR'),
            Generators::bool(),
        )->then(function (string $from, string $to, bool $withTotalValue): void {
            $totalValue = $withTotalValue ? new MoneyAmount('100', 'EUR') : null;

            if ($from !== $to && !$withTotalValue) {
                $this->expectException(InvalidCustomsException::class);
                $this->buildRequest($from, $to, null, $totalValue);

                return;
            }

            $request = $this->buildRequest($from, $to, null, $totalValue);
            $this->assertSame($from !== $to, $request->isCrossBorder());
        });
    }

    /**
     * Feature: ship-endpoint, Property 5: For any insurance amount provided without a currency, the constructed
     * Insurance SHALL carry the currency EUR; for any amount provided with a currency, the constructed Insurance SHALL
     * preserve that currency (upper-cased).
     */
    #[ErisRepeat(100)]
    public function testInsuranceDefaultCurrency(): void
    {
        $this->forAll(Generators::choose(0, 100000), Generators::elements('usd', 'GBP', 'chf'))
            ->then(function (int $cents, string $currency): void {
                $amount = $cents / 100;

                $this->assertSame('EUR', (new Insurance($amount))->currency);
                $this->assertSame(strtoupper($currency), (new Insurance($amount, $currency))->currency);
            });
    }

    private function buildRequest(
        string $fromCountry,
        string $toCountry,
        ?Customs $customs = null,
        ?MoneyAmount $totalValue = null,
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
