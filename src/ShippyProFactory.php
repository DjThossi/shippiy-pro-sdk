<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk;

use DjThossi\ShippyProSdk\Api\PingApi;
use DjThossi\ShippyProSdk\Api\StatusApi;
use DjThossi\ShippyProSdk\Http\Client;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use DjThossi\ShippyProSdk\Mapper\HeaderMapper;
use DjThossi\ShippyProSdk\Mapper\PingMapper;
use DjThossi\ShippyProSdk\Mapper\StatusMapper;

final readonly class ShippyProFactory
{
    public function __construct(
        private ShippyProConfiguration $configuration,
    ) {
    }

    public function createPingApi(): PingApi
    {
        return new PingApi(
            $this->createClient(),
            $this->createPingMapper(),
            $this->createHeaderMapper(),
            $this->createErrorMapper(),
        );
    }

    public function createStatusApi(): StatusApi
    {
        return new StatusApi(
            $this->createClient(),
            $this->createStatusMapper(),
            $this->createHeaderMapper(),
            $this->createErrorMapper(),
        );
    }

    private function createClient(): Client
    {
        return new Client(
            $this->configuration->getBaseUrl(),
            $this->configuration->getApiKey(),
            $this->configuration->getRequestTimeout(),
            $this->configuration->getPartnerName(),
        );
    }

    private function createPingMapper(): PingMapper
    {
        return new PingMapper();
    }

    private function createStatusMapper(): StatusMapper
    {
        return new StatusMapper();
    }

    private function createHeaderMapper(): HeaderMapper
    {
        return new HeaderMapper();
    }

    private function createErrorMapper(): ErrorMapper
    {
        return new ErrorMapper();
    }
}
