<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class PingResponseBody
{
    /**
     * The `Result` field is optional per v1.json. It is null when the API
     * omits it (for example when authentication fails, which ShippyPro
     * reports with HTTP 200 and an `{"Error": ...}` body instead of `Result`).
     */
    public function __construct(
        public ?bool $result,
    ) {
    }
}
