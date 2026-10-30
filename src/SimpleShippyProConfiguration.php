<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\PartnerName;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;

final readonly class SimpleShippyProConfiguration implements ShippyProConfiguration
{
    public const string DEFAULT_BASE_URL = 'https://api.shippypro.com';
    public const int DEFAULT_REQUEST_TIMEOUT = 10;

    private ApiKey $apiKey;
    private BaseUrl $baseUrl;
    private RequestTimeout $requestTimeout;
    private ?PartnerName $partnerName;

    public function __construct(
        ApiKey $apiKey,
        ?BaseUrl $baseUrl = null,
        ?RequestTimeout $requestTimeout = null,
        ?PartnerName $partnerName = null,
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl ?? new BaseUrl(self::DEFAULT_BASE_URL);
        $this->requestTimeout = $requestTimeout ?? new RequestTimeout(self::DEFAULT_REQUEST_TIMEOUT);
        $this->partnerName = $partnerName;
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): BaseUrl
    {
        return $this->baseUrl;
    }

    public function getRequestTimeout(): RequestTimeout
    {
        return $this->requestTimeout;
    }

    public function getPartnerName(): ?PartnerName
    {
        return $this->partnerName;
    }
}
