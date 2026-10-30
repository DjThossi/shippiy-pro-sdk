<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Mapper;

use DjThossi\ShippyProSdk\Domain\ApiError;
use DjThossi\ShippyProSdk\Mapper\ErrorMapper;
use PHPUnit\Framework\TestCase;

final class ErrorMapperTest extends TestCase
{
    private ErrorMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ErrorMapper();
    }

    public function testReturnsNullForSuccessBody(): void
    {
        $this->assertNull($this->mapper->mapFromArray(['Result' => true]));
        $this->assertNull($this->mapper->mapFromArray(['status' => 'HEALTHY', 'version' => '2.0.0']));
        $this->assertNull($this->mapper->mapFromArray([]));
    }

    public function testMapsV1ErrorBody(): void
    {
        $error = $this->mapper->mapFromArray(['Error' => 'Authentication Failed']);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertSame('Authentication Failed', $error->detail);
        $this->assertNull($error->status);
    }

    public function testIgnoresEmptyV1ErrorField(): void
    {
        $this->assertNull($this->mapper->mapFromArray(['Error' => '   ']));
    }

    public function testMapsV1NokEnvelopeWithErrorMessage(): void
    {
        $error = $this->mapper->mapFromArray(['Result' => 'NOK', 'ErrorMessage' => 'Something went wrong']);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertSame('Something went wrong', $error->detail);
    }

    public function testMapsV1NokEnvelopeWithoutErrorMessage(): void
    {
        $error = $this->mapper->mapFromArray(['Result' => 'NOK']);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertSame('NOK', $error->detail);
    }

    public function testV1OkResultIsNotAnError(): void
    {
        $this->assertNull($this->mapper->mapFromArray(['Result' => 'OK']));
    }

    public function testMapsV2ProblemDetail(): void
    {
        $error = $this->mapper->mapFromArray([
            'type' => 'https://api.shippypro.com/problems/unauthorized',
            'title' => 'Unauthorized',
            'status' => 401,
            'detail' => 'Authentication failed',
            'instance' => '/v2/status/req_1',
        ]);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertSame('Authentication failed', $error->detail);
        $this->assertSame('https://api.shippypro.com/problems/unauthorized', $error->type);
        $this->assertSame('Unauthorized', $error->title);
        $this->assertSame(401, $error->status);
        $this->assertSame('/v2/status/req_1', $error->instance);
        $this->assertFalse($error->hasFieldErrors());
    }

    public function testMapsV2ValidationErrorWithFieldErrors(): void
    {
        $error = $this->mapper->mapFromArray([
            'type' => 'https://api.shippypro.com/problems/validation-error',
            'title' => 'Validation Error',
            'status' => 422,
            'detail' => 'The request body contains invalid fields.',
            'errors' => [
                ['detail' => 'Postal code is required', 'pointer' => '/toAddress/postalCode', 'code' => 'REQUIRED'],
                ['detail' => 'Carrier not found', 'parameter' => 'carrier', 'code' => 'INVALID_ENUM'],
            ],
        ]);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertTrue($error->hasFieldErrors());
        $this->assertCount(2, $error->fieldErrors);
        $this->assertSame('Postal code is required', $error->fieldErrors[0]->detail);
        $this->assertSame('/toAddress/postalCode', $error->fieldErrors[0]->pointer);
        $this->assertSame('REQUIRED', $error->fieldErrors[0]->code);
        $this->assertSame('carrier', $error->fieldErrors[1]->parameter);
    }

    public function testProblemDetailFallsBackToTitleWhenDetailMissing(): void
    {
        $error = $this->mapper->mapFromArray(['title' => 'Unauthorized', 'status' => 401]);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertSame('Unauthorized', $error->detail);
    }

    public function testProblemDetailIgnoresMalformedFieldErrors(): void
    {
        $error = $this->mapper->mapFromArray([
            'title' => 'Validation Error',
            'errors' => [
                ['pointer' => '/x'], // no detail -> ignored
                'not-an-array',       // ignored
                ['detail' => 'valid'],
            ],
        ]);

        $this->assertInstanceOf(ApiError::class, $error);
        $this->assertCount(1, $error->fieldErrors);
        $this->assertSame('valid', $error->fieldErrors[0]->detail);
    }
}
