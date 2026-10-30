<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Property;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;
use DjThossi\ShippyProSdk\Mapper\ShipMapper;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

final class ShipMapperPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Feature: ship-endpoint, Property 1: For any valid ShipRequest, serializing it through ShipMapper::encodeParams
     * SHALL yield a ShipParams payload where every provided optional member is present under its exact v1.json field
     * name and every absent optional member is omitted.
     */
    #[ErisRepeat(100)]
    public function testShipParamsRequestRoundTrip(): void
    {
        $mapper = new ShipMapper();

        $this->forAll(
            Generators::string(),
            Generators::choose(1, 9999),
            Generators::bool(),
        )->then(function (string $transactionId, int $carrierId, bool $withRate) use ($mapper): void {
            $address = new Address('N', '', 'S1', '', 'City', 'ST', '1', new CountryCode('US'), '1', 'a@b.c');
            $request = new ShipRequest(
                $address,
                $address,
                new ParcelCollection(new Parcel(5.0, 5.0, 5.0, 10.0)),
                $transactionId,
                'Goods',
                'MyDHL',
                'EXPRESS',
                $carrierId,
                rateId: $withRate ? 'RATE-1' : null,
            );

            $params = $mapper->encodeParams($request);

            $this->assertSame($transactionId, $params[ShipMapper::FIELD_TRANSACTION_ID]);
            $this->assertSame($carrierId, $params[ShipMapper::FIELD_CARRIER_ID]);

            if ($withRate) {
                $this->assertSame('RATE-1', $params[ShipMapper::FIELD_RATE_ID]);
            } else {
                $this->assertArrayNotHasKey(ShipMapper::FIELD_RATE_ID, $params);
            }
        });
    }

    /**
     * Feature: ship-endpoint, Property 2: For any of the three LabelURL shapes - an absent/empty array, a single
     * string, or an array of strings - ShipMapper::mapLabelUrl SHALL produce a LabelUrlCollection that is empty for the
     * empty case, contains exactly the one label for the single-string case, and contains one label per string in the
     * original order for the array case.
     */
    #[ErisRepeat(100)]
    public function testLabelUrlPolymorphicNormalization(): void
    {
        $mapper = new ShipMapper();

        $this->forAll(Generators::seq(Generators::string()))
            ->then(function (array $labels) use ($mapper): void {
                $values = array_values($labels);

                // Array-of-strings case preserves order and count.
                $fromArray = $mapper->mapLabelUrl([ShipMapper::FIELD_LABEL_URL => $values]);
                $expected = array_map(static fn ($v): string => (string) $v, $values);
                $this->assertSame($expected, $fromArray->all());

                // Single-string case yields exactly one label.
                $single = $mapper->mapLabelUrl([ShipMapper::FIELD_LABEL_URL => 'one-label']);
                $this->assertSame(['one-label'], $single->all());

                // Empty/absent case yields an empty collection.
                $this->assertTrue($mapper->mapLabelUrl([ShipMapper::FIELD_LABEL_URL => []])->isEmpty());
                $this->assertTrue($mapper->mapLabelUrl([])->isEmpty());
            });
    }

    /**
     * Feature: ship-endpoint, Property 7: For any decoded response map that omits at least one required field of the
     * Ship response body, ShipMapper::mapResponseBody SHALL throw MissingRequiredFieldsException naming exactly the
     * omitted required field(s).
     */
    #[ErisRepeat(100)]
    public function testResponseMapperRejectsMissingRequiredFields(): void
    {
        $mapper = new ShipMapper();

        // 0 = omit Result, 1 = omit Status, 2 = omit both.
        $this->forAll(Generators::choose(0, 2))
            ->then(function (int $omit) use ($mapper): void {
                $data = [ShipMapper::FIELD_RESULT => 'OK', ShipMapper::FIELD_STATUS => '1'];
                $expected = [];
                if ($omit === 0 || $omit === 2) {
                    unset($data[ShipMapper::FIELD_RESULT]);
                    $expected[] = ShipMapper::FIELD_RESULT;
                }
                if ($omit === 1 || $omit === 2) {
                    unset($data[ShipMapper::FIELD_STATUS]);
                    $expected[] = ShipMapper::FIELD_STATUS;
                }

                try {
                    $mapper->mapResponseBody($data);
                    $this->fail('Expected MissingRequiredFieldsException');
                } catch (MissingRequiredFieldsException $e) {
                    foreach ($expected as $field) {
                        $this->assertStringContainsString($field, $e->getMessage());
                    }
                }
            });
    }
}
