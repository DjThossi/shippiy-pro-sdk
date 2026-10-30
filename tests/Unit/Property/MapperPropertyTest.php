<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Property;

use DjThossi\ShippyProSdk\Domain\StatusResponseBody;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;
use DjThossi\ShippyProSdk\Mapper\PingMapper;
use DjThossi\ShippyProSdk\Mapper\StatusMapper;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

final class MapperPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Feature: shippy-pro-sdk, Property 1: For any StatusResponseBody with a status and version, encoding it to its
     * JSON field map and mapping that map back through StatusMapper::mapResponseBody SHALL produce an equivalent
     * StatusResponseBody.
     */
    #[ErisRepeat(100)]
    public function testStatusBodyRoundTrip(): void
    {
        $mapper = new StatusMapper();

        $this->forAll(Generators::string(), Generators::string())
            ->then(function (string $status, string $version) use ($mapper): void {
                $original = new StatusResponseBody($status, $version);

                $fieldMap = [
                    StatusMapper::FIELD_STATUS => $original->status,
                    StatusMapper::FIELD_VERSION => $original->version,
                ];
                $roundTripped = $mapper->mapResponseBody($fieldMap);

                $this->assertSame($original->status, $roundTripped->status);
                $this->assertSame($original->version, $roundTripped->version);
            });
    }

    /**
     * Feature: shippy-pro-sdk, Property 2: For any boolean result, building the JSON field map {"Result": result} and
     * mapping it through PingMapper::mapResponseBody SHALL produce a PingResponseBody whose result equals the original
     * boolean.
     */
    #[ErisRepeat(100)]
    public function testPingBodyRoundTrip(): void
    {
        $mapper = new PingMapper();

        $this->forAll(Generators::bool())
            ->then(function (bool $result) use ($mapper): void {
                $body = $mapper->mapResponseBody([PingMapper::FIELD_RESULT => $result]);

                $this->assertSame($result, $body->result);
            });
    }

    /**
     * Feature: shippy-pro-sdk, Property 3: For any input map that omits at least one required field of a response body,
     * the corresponding Mapper SHALL throw MissingRequiredFieldsException naming exactly the omitted required field(s).
     */
    #[ErisRepeat(100)]
    public function testStatusMapperRejectsMissingRequiredFields(): void
    {
        $mapper = new StatusMapper();

        // 0 = omit status, 1 = omit version, 2 = omit both.
        $this->forAll(Generators::choose(0, 2), Generators::string(), Generators::string())
            ->then(function (int $omit, string $status, string $version) use ($mapper): void {
                $data = [
                    StatusMapper::FIELD_STATUS => $status,
                    StatusMapper::FIELD_VERSION => $version,
                ];
                $expectedMissing = [];
                if ($omit === 0 || $omit === 2) {
                    unset($data[StatusMapper::FIELD_STATUS]);
                    $expectedMissing[] = StatusMapper::FIELD_STATUS;
                }
                if ($omit === 1 || $omit === 2) {
                    unset($data[StatusMapper::FIELD_VERSION]);
                    $expectedMissing[] = StatusMapper::FIELD_VERSION;
                }

                try {
                    $mapper->mapResponseBody($data);
                    $this->fail('Expected MissingRequiredFieldsException was not thrown');
                } catch (MissingRequiredFieldsException $exception) {
                    foreach ($expectedMissing as $field) {
                        $this->assertStringContainsString($field, $exception->getMessage());
                    }
                }
            });
    }
}
