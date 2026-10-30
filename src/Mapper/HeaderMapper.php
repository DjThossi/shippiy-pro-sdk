<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Mapper;

use function array_change_key_case;

use const CASE_LOWER;

use function ctype_digit;

use DjThossi\ShippyProSdk\Domain\RateLimit;
use DjThossi\ShippyProSdk\Domain\ResponseHeaders;

use function preg_match;

use Psr\Http\Message\ResponseInterface;

use function strtolower;

final readonly class HeaderMapper
{
    public const string HEADER_RATE_LIMIT_LIMIT = 'X-RateLimit-Limit';
    public const string HEADER_RATE_LIMIT_REMAINING = 'X-RateLimit-Remaining';
    public const string HEADER_RATE_LIMIT_RESET = 'X-RateLimit-Reset';
    public const string HEADER_RATE_LIMIT = 'RateLimit';
    public const string HEADER_RATE_LIMIT_POLICY = 'RateLimit-Policy';

    public function mapResponseHeaders(ResponseInterface $response): ResponseHeaders
    {
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = $response->getHeaderLine($name);
        }

        return new ResponseHeaders($headers, $this->mapRateLimit($headers));
    }

    /**
     * @param array<string, string> $headers
     */
    private function mapRateLimit(array $headers): RateLimit
    {
        $lower = array_change_key_case($headers, CASE_LOWER);

        $limit = $this->intHeader($lower, self::HEADER_RATE_LIMIT_LIMIT);
        $remaining = $this->intHeader($lower, self::HEADER_RATE_LIMIT_REMAINING);
        $reset = $this->intHeader($lower, self::HEADER_RATE_LIMIT_RESET);

        // Fall back to the IETF draft headers when the legacy ones are absent.
        $limit ??= $this->parseToken($lower, self::HEADER_RATE_LIMIT_POLICY, 'q');
        $remaining ??= $this->parseToken($lower, self::HEADER_RATE_LIMIT, 'r');
        $reset ??= $this->parseToken($lower, self::HEADER_RATE_LIMIT, 't');

        return new RateLimit($limit, $remaining, $reset);
    }

    /**
     * @param array<string, string> $lowerHeaders
     */
    private function intHeader(array $lowerHeaders, string $name): ?int
    {
        $value = $lowerHeaders[strtolower($name)] ?? null;
        if ($value === null || !ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Parses a token such as `r=42` from an IETF rate-limit header value like
     * `"standard";r=42;t=18`.
     *
     * @param array<string, string> $lowerHeaders
     */
    private function parseToken(array $lowerHeaders, string $name, string $token): ?int
    {
        $value = $lowerHeaders[strtolower($name)] ?? null;
        if ($value === null) {
            return null;
        }

        if (preg_match('/\b' . $token . '=(\d+)/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
