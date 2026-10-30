<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit;

use DjThossi\ShippyProSdk\Api\PingApi;
use DjThossi\ShippyProSdk\Api\StatusApi;
use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\ShippyProFactory;
use DjThossi\ShippyProSdk\SimpleShippyProConfiguration;
use PHPUnit\Framework\TestCase;

final class ShippyProFactoryTest extends TestCase
{
    private ShippyProFactory $factory;

    protected function setUp(): void
    {
        $configuration = new SimpleShippyProConfiguration(
            new ApiKey('my-key'),
            null,
            null,
            new PartnerName('my-platform'),
        );
        $this->factory = new ShippyProFactory($configuration);
    }

    public function testCreatePingApiReturnsPingApi(): void
    {
        $this->assertInstanceOf(PingApi::class, $this->factory->createPingApi());
    }

    public function testCreateStatusApiReturnsStatusApi(): void
    {
        $this->assertInstanceOf(StatusApi::class, $this->factory->createStatusApi());
    }

    public function testCreatePingApiReturnsFreshInstances(): void
    {
        $this->assertNotSame($this->factory->createPingApi(), $this->factory->createPingApi());
    }

    public function testWorksWithMinimalConfiguration(): void
    {
        $factory = new ShippyProFactory(new SimpleShippyProConfiguration(new ApiKey('my-key')));

        $this->assertInstanceOf(PingApi::class, $factory->createPingApi());
        $this->assertInstanceOf(StatusApi::class, $factory->createStatusApi());
    }
}
