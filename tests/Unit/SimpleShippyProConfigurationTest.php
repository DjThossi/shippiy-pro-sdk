<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;
use DjThossi\ShippyProSdk\ShippyProConfiguration;
use DjThossi\ShippyProSdk\SimpleShippyProConfiguration;
use PHPUnit\Framework\TestCase;

final class SimpleShippyProConfigurationTest extends TestCase
{
    public function testImplementsShippyProConfigurationInterface(): void
    {
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'));

        $this->assertInstanceOf(ShippyProConfiguration::class, $config);
    }

    public function testReturnsGivenApiKey(): void
    {
        $apiKey = new ApiKey('my-key');
        $config = new SimpleShippyProConfiguration($apiKey);

        $this->assertSame($apiKey, $config->getApiKey());
    }

    public function testUsesDefaultBaseUrlWhenNotProvided(): void
    {
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'));

        $this->assertInstanceOf(BaseUrl::class, $config->getBaseUrl());
        $this->assertSame('https://api.shippypro.com', $config->getBaseUrl()->value);
    }

    public function testUsesDefaultRequestTimeoutWhenNotProvided(): void
    {
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'));

        $this->assertInstanceOf(RequestTimeout::class, $config->getRequestTimeout());
        $this->assertSame(10, $config->getRequestTimeout()->seconds);
    }

    public function testPartnerNameDefaultsToNull(): void
    {
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'));

        $this->assertNull($config->getPartnerName());
    }

    public function testUsesProvidedBaseUrl(): void
    {
        $baseUrl = new BaseUrl('https://staging.shippypro.example');
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'), $baseUrl);

        $this->assertSame($baseUrl, $config->getBaseUrl());
    }

    public function testUsesProvidedRequestTimeout(): void
    {
        $timeout = new RequestTimeout(30);
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'), null, $timeout);

        $this->assertSame($timeout, $config->getRequestTimeout());
    }

    public function testUsesProvidedPartnerName(): void
    {
        $partnerName = new PartnerName('my-platform');
        $config = new SimpleShippyProConfiguration(new ApiKey('my-key'), null, null, $partnerName);

        $this->assertSame($partnerName, $config->getPartnerName());
    }
}
