<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Mapper;

use DjThossi\ShippyProSdk\Domain\StatusResponseBody;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;
use DjThossi\ShippyProSdk\Mapper\StatusMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusMapperTest extends TestCase
{
    private StatusMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StatusMapper();
    }

    public function testMapResponseBodyWithCompleteData(): void
    {
        $body = $this->mapper->mapResponseBody([
            'status' => 'HEALTHY',
            'version' => '2.0.0',
        ]);

        $this->assertInstanceOf(StatusResponseBody::class, $body);
        $this->assertSame('HEALTHY', $body->status);
        $this->assertSame('2.0.0', $body->version);
    }

    public function testMapResponseBodyIgnoresAdditionalFields(): void
    {
        $body = $this->mapper->mapResponseBody([
            'status' => 'HEALTHY',
            'version' => '2.0.0',
            'extra' => 'ignored',
        ]);

        $this->assertSame('HEALTHY', $body->status);
        $this->assertSame('2.0.0', $body->version);
    }

    #[DataProvider('missingFieldProvider')]
    public function testMapResponseBodyMissingRequiredFieldsThrowsException(array $data, string $expectedMessage): void
    {
        $this->expectException(MissingRequiredFieldsException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->mapper->mapResponseBody($data);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function missingFieldProvider(): array
    {
        return [
            'missing status' => [
                ['version' => '2.0.0'],
                'Missing required fields: status',
            ],
            'missing version' => [
                ['status' => 'HEALTHY'],
                'Missing required fields: version',
            ],
            'missing both' => [
                [],
                'Missing required fields: status, version',
            ],
        ];
    }
}
