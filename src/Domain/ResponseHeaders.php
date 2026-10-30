<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use function array_change_key_case;

use const CASE_LOWER;

use function strtolower;

final readonly class ResponseHeaders
{
    /**
     * @var array<string, string> header name (lowercased) => first value
     */
    private array $headers;

    private RateLimit $rateLimit;

    /**
     * @param array<string, string> $headers header name => value (first value per header)
     */
    public function __construct(array $headers, RateLimit $rateLimit)
    {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->rateLimit = $rateLimit;
    }

    public function get(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getRequestId(): ?string
    {
        return $this->get('X-Request-Id');
    }

    public function getRateLimit(): RateLimit
    {
        return $this->rateLimit;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->headers;
    }
}
