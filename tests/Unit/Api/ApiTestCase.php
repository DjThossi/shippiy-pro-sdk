<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

abstract class ApiTestCase extends TestCase
{
    /**
     * @param array<string, string[]> $headers
     */
    protected function createResponseMock(int $statusCode = 200, array $headers = [], string $body = ''): Response
    {
        return new Response($statusCode, $headers, $body);
    }
}
