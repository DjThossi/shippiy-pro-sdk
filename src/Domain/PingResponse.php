<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

final readonly class PingResponse
{
    public function __construct(
        public StatusCode $statusCode,
        public ResponseHeaders $headers,
        public PingResponseBody $responseBody,
        public ?ApiError $error = null,
    ) {
    }

    public function hasError(): bool
    {
        return $this->error instanceof ApiError;
    }
}
