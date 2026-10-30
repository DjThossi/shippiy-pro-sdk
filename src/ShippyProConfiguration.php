<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;

interface ShippyProConfiguration
{
    public function getApiKey(): ApiKey;

    public function getBaseUrl(): BaseUrl;

    public function getRequestTimeout(): RequestTimeout;

    public function getPartnerName(): ?PartnerName;
}
