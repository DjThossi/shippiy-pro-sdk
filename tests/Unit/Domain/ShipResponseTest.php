<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\ApiError;
use DjThossi\ShippyProSdk\Domain\LabelUrlCollection;
use DjThossi\ShippyProSdk\Domain\RateLimit;
use DjThossi\ShippyProSdk\Domain\ResponseHeaders;
use DjThossi\ShippyProSdk\Domain\ShipResponse;
use DjThossi\ShippyProSdk\Domain\ShipResponseBody;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use DjThossi\ShippyProSdk\Domain\StatusCode;
use PHPUnit\Framework\TestCase;

final class ShipResponseTest extends TestCase
{
    public function testSuccessResponseHasNoError(): void
    {
        $body = new ShipResponseBody('OK', ShipStatus::Ok, new LabelUrlCollection('u'));
        $response = new ShipResponse($this->headers(), $this->headersObject(), $body);

        $this->assertFalse($response->hasError());
        $this->assertNull($response->error);
        $this->assertSame($body, $response->responseBody);
    }

    public function testErrorResponseExposesApiError(): void
    {
        $error = new ApiError('Authentication Failed');
        $response = new ShipResponse(new StatusCode(401), $this->headersObject(), null, $error);

        $this->assertTrue($response->hasError());
        $this->assertSame($error, $response->error);
        $this->assertNull($response->responseBody);
    }

    private function headers(): StatusCode
    {
        return new StatusCode(200);
    }

    private function headersObject(): ResponseHeaders
    {
        return new ResponseHeaders([], new RateLimit(null, null, null));
    }
}
