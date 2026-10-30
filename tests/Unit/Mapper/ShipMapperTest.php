<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Mapper;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CarrierOption;
use DjThossi\ShippyProSdk\Domain\CarrierOptionCollection;
use DjThossi\ShippyProSdk\Domain\CashOnDelivery;
use DjThossi\ShippyProSdk\Domain\CashOnDeliveryType;
use DjThossi\ShippyProSdk\Domain\Cn22Item;
use DjThossi\ShippyProSdk\Domain\Cn22ItemCollection;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\CustomInfoContentsType;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\Insurance;
use DjThossi\ShippyProSdk\Domain\LabelFormatting;
use DjThossi\ShippyProSdk\Domain\LabelType;
use DjThossi\ShippyProSdk\Domain\MoneyAmount;
use DjThossi\ShippyProSdk\Domain\NonDeliveryOption;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelAttributes;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;
use DjThossi\ShippyProSdk\Mapper\ShipMapper;

use function json_decode;

use const JSON_THROW_ON_ERROR;

use PHPUnit\Framework\TestCase;

final class ShipMapperTest extends TestCase
{
    private ShipMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ShipMapper();
    }

    public function testEncodeRequestWrapsEnvelopeAndOmitsAbsentOptionals(): void
    {
        $json = $this->mapper->encodeRequest($this->minimalRequest());
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Ship', $decoded['Method']);
        $params = $decoded['Params'];
        $this->assertSame('ORDER1', $params['TransactionID']);
        $this->assertSame('MyDHL', $params['CarrierName']);
        $this->assertSame(23, $params['CarrierID']);
        $this->assertArrayHasKey('to_address', $params);
        $this->assertArrayHasKey('from_address', $params);
        $this->assertArrayHasKey('parcels', $params);
        // Absent optionals must be omitted.
        $this->assertArrayNotHasKey('RateID', $params);
        $this->assertArrayNotHasKey('TotalValue', $params);
        $this->assertArrayNotHasKey('Insurance', $params);
        $this->assertArrayNotHasKey('CarrierOptions', $params);
    }

    public function testEncodeParamsSerializesProvidedOptionalsUnderExactFieldNames(): void
    {
        $from = $this->address('DE');
        $to = $this->address('US');
        $request = new ShipRequest(
            $to,
            $from,
            new ParcelCollection(new Parcel(5.0, 5.0, 5.0, 10.0)),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
            rateId: 'R1',
            totalValue: new MoneyAmount('123', 'EUR'),
            customs: new Customs(
                Incoterm::Exw,
                'ACC-1',
                null,
                new Cn22ItemCollection(new Cn22Item('x', 1.0, 2, 3.0, new CountryCode('IT'), 'USD', '123456')),
            ),
            carrierOptions: new CarrierOptionCollection(new CarrierOption('upsdangerousgoods', '1')),
            labelFormatting: new LabelFormatting(LabelType::Pdf, 10.16, 20.32, 90, 1, true),
        );

        $params = $this->mapper->encodeParams($request);

        $this->assertSame('R1', $params['RateID']);
        $this->assertSame('123 EUR', $params['TotalValue']);
        $this->assertSame('EXW', $params['Incoterm']);
        $this->assertSame('ACC-1', $params['BillAccountNumber']);
        $this->assertSame('123456', $params['CN22Info'][0]['HSCode']);
        $this->assertSame('IT', $params['CN22Info'][0]['OriginCountry']);
        $this->assertSame('upsdangerousgoods', $params['CarrierOptions'][0]['name']);
        $this->assertSame('PDF', $params['LabelType']);
        $this->assertSame(10.16, $params['LabelWidth']);
        $this->assertSame(20.32, $params['LabelHeight']);
        $this->assertSame(90, $params['Rotate']);
        $this->assertSame(1, $params['PageNumber']);
        $this->assertTrue($params['SplitPages']);
    }

    public function testMapResponseBodyComplete(): void
    {
        $body = $this->mapper->mapResponseBody([
            'Result' => 'OK',
            'Status' => '1',
            'NewOrderID' => '123',
            'LabelURL' => 'https://cdn/label.pdf',
            'TrackingNumber' => 'TN1',
            'DeliveryCarrier' => 'PostNL',
            'PDF' => ['https://cdn/a.pdf'],
            'ErrorType' => ['code' => 'X', 'name' => 'n', 'typeDescription' => 'd'],
            'ErrorSuggestion' => ['errorDescription' => 'e', 'solution' => 's'],
            'Parcels' => [['TrackingNumber' => 'TN1', 'PDF' => 'p']],
        ]);

        $this->assertSame('OK', $body->result);
        $this->assertSame(ShipStatus::Ok, $body->status);
        $this->assertSame(['https://cdn/label.pdf'], $body->labelUrls->all());
        $this->assertSame('123', $body->newOrderId);
        $this->assertSame('PostNL', $body->deliveryCarrier);
        $this->assertSame(['https://cdn/a.pdf'], $body->pdf);
        $this->assertSame('X', $body->errorType?->code);
        $this->assertSame('s', $body->errorSuggestion?->solution);
        $this->assertSame(1, $body->parcels?->count());
        $this->assertFalse($body->hasBusinessError());
    }

    public function testMapResponseBodyMinimal(): void
    {
        $body = $this->mapper->mapResponseBody(['Result' => 'OK', 'Status' => '1']);

        $this->assertTrue($body->labelUrls->isEmpty());
        $this->assertNull($body->newOrderId);
        $this->assertNull($body->errorType);
        $this->assertNull($body->parcels);
        $this->assertSame([], $body->pdf);
    }

    public function testMapResponseBodyErrorStatus(): void
    {
        $body = $this->mapper->mapResponseBody(['Result' => 'KO', 'Status' => '2', 'ErrorMessage' => 'boom']);

        $this->assertSame(ShipStatus::Error, $body->status);
        $this->assertSame('boom', $body->errorMessage);
        $this->assertTrue($body->hasBusinessError());
    }

    public function testMapResponseBodyMissingRequiredFieldsThrows(): void
    {
        $this->expectException(MissingRequiredFieldsException::class);
        $this->expectExceptionMessage('Missing required fields: Result, Status');

        $this->mapper->mapResponseBody([]);
    }

    public function testMapLabelUrlEmptyArray(): void
    {
        $this->assertTrue($this->mapper->mapLabelUrl(['LabelURL' => []])->isEmpty());
        $this->assertTrue($this->mapper->mapLabelUrl([])->isEmpty());
    }

    public function testMapLabelUrlSingleString(): void
    {
        $collection = $this->mapper->mapLabelUrl(['LabelURL' => 'https://cdn/one.pdf']);

        $this->assertSame(1, $collection->count());
        $this->assertSame(['https://cdn/one.pdf'], $collection->all());
    }

    public function testMapLabelUrlArrayOfStringsPreservesOrder(): void
    {
        $collection = $this->mapper->mapLabelUrl([
            'LabelURL' => ['https://cdn/1.zpl', 'https://cdn/2.zpl'],
        ]);

        $this->assertSame(['https://cdn/1.zpl', 'https://cdn/2.zpl'], $collection->all());
    }

    public function testEncodeParamsSerializesAllOptionalBranches(): void
    {
        $address = $this->address('US');
        $request = new ShipRequest(
            $address,
            $address,
            new ParcelCollection(new Parcel(
                5.0,
                5.0,
                5.0,
                10.0,
                attributes: new ParcelAttributes('p1', 'Box', 'IOSS123'),
            )),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
            orderId: 'O1',
            insurance: new Insurance(10.0, 'USD'),
            cashOnDelivery: new CashOnDelivery(5.0, 'GBP', CashOnDeliveryType::Check),
            note: 'n',
            carrierNote: 'cn',
            userNote: 'un',
            paymentMethod: 'Paypal',
            customs: new Customs(
                Incoterm::Dap,
                null,
                CustomInfoContentsType::Gift,
                null,
                NonDeliveryOption::Abandon,
            ),
            isReturn: true,
            returnCarrierName: 'Generic',
            returnCarrierService: 'Standard',
            returnCarrierId: 6,
            returnInsurance: new Insurance(2.0),
            returnCarrierOptions: new CarrierOptionCollection(new CarrierOption('upsdangerousgoods', '1')),
            returnTo: $this->address('IT'),
            attributes: new ParcelAttributes(null, null, 'IOSS999'),
        );

        $params = $this->mapper->encodeParams($request);

        $this->assertSame('O1', $params['OrderID']);
        $this->assertSame(10.0, $params['Insurance']);
        $this->assertSame('USD', $params['InsuranceCurrency']);
        $this->assertSame(5.0, $params['CashOnDelivery']);
        $this->assertSame('GBP', $params['CashOnDeliveryCurrency']);
        $this->assertSame(2, $params['CashOnDeliveryType']);
        $this->assertSame('n', $params['Note']);
        $this->assertSame('cn', $params['CarrierNote']);
        $this->assertSame('un', $params['UserNote']);
        $this->assertSame('Paypal', $params['PaymentMethod']);
        $this->assertSame('gift', $params['CustomInfoContentsType']);
        $this->assertSame('abandon', $params['non_delivery_option']);
        $this->assertTrue($params['IsReturn']);
        $this->assertSame('Generic', $params['ReturnCarrierName']);
        $this->assertSame('Standard', $params['ReturnCarrierService']);
        $this->assertSame(6, $params['ReturnCarrierID']);
        $this->assertSame(2.0, $params['ReturnInsurance']);
        $this->assertSame('EUR', $params['ReturnInsuranceCurrency']);
        $this->assertSame('upsdangerousgoods', $params['ReturnCarrierOptions'][0]['name']);
        $this->assertSame('IT', $params['return_to']['country']);
        $this->assertSame('IOSS999', $params['Attributes']['sp_ioss']);
        $this->assertSame('IOSS123', $params['parcels'][0]['Attributes']['sp_ioss']);
        $this->assertSame('p1', $params['parcels'][0]['Attributes']['parcelID']);
        $this->assertSame(1, $params['parcels'][0]['package'] ?? 1);
    }

    public function testMapResponseBodyMapsAdditionalTrackingAndReturnParcels(): void
    {
        $body = $this->mapper->mapResponseBody([
            'Result' => 'OK',
            'Status' => '1',
            'ZPL' => ['https://cdn/a.zpl'],
            'AdditionalTrackingNumbers' => [['n' => '1'], 'ignored-scalar'],
            'ReturnParcels' => [['TrackingNumber' => 'RTN', 'ZPL' => 'z']],
        ]);

        $this->assertSame(['https://cdn/a.zpl'], $body->zpl);
        $this->assertCount(1, $body->additionalTrackingNumbers);
        $this->assertSame(1, $body->returnParcels?->count());
        $this->assertSame('RTN', $body->returnParcels?->all()[0]->trackingNumber);
    }

    public function testEncodeParamsSerializesParcelPackage(): void
    {
        $address = $this->address('US');
        $request = new ShipRequest(
            $address,
            $address,
            new ParcelCollection(new Parcel(5.0, 5.0, 5.0, 10.0, package: 1)),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
        );

        $params = $this->mapper->encodeParams($request);

        $this->assertSame(1, $params['parcels'][0]['package']);
    }

    public function testMapResponseBodySkipsNonArrayParcelEntries(): void
    {
        $body = $this->mapper->mapResponseBody([
            'Result' => 'OK',
            'Status' => '1',
            'Parcels' => ['not-an-array', ['TrackingNumber' => 'TN1']],
        ]);

        $this->assertSame(1, $body->parcels?->count());
        $this->assertSame('TN1', $body->parcels?->all()[0]->trackingNumber);
    }

    private function minimalRequest(): ShipRequest
    {
        return new ShipRequest(
            $this->address('US'),
            $this->address('US'),
            new ParcelCollection(new Parcel(5.0, 5.0, 5.0, 10.0)),
            'ORDER1',
            'Goods',
            'MyDHL',
            'EXPRESS',
            23,
        );
    }

    private function address(string $country): Address
    {
        return new Address('N', '', 'S1', '', 'City', 'ST', '1', new CountryCode($country), '1', 'a@b.c');
    }
}
