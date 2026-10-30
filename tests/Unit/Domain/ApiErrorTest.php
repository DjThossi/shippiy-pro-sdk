<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\ApiError;
use DjThossi\ShippyProSdk\Domain\FieldError;
use PHPUnit\Framework\TestCase;

final class ApiErrorTest extends TestCase
{
    public function testMinimalErrorHoldsDetail(): void
    {
        $error = new ApiError('Authentication Failed');

        $this->assertSame('Authentication Failed', $error->detail);
        $this->assertNull($error->type);
        $this->assertNull($error->title);
        $this->assertNull($error->status);
        $this->assertNull($error->instance);
        $this->assertSame([], $error->fieldErrors);
        $this->assertFalse($error->hasFieldErrors());
    }

    public function testFullErrorHoldsAllFields(): void
    {
        $fieldError = new FieldError('Postal code is required', '/toAddress/postalCode', null, 'REQUIRED');
        $error = new ApiError(
            'The request body contains invalid fields.',
            'https://api.shippypro.com/problems/validation-error',
            'Validation Error',
            422,
            '/v2/shipments/req_abc',
            [$fieldError],
        );

        $this->assertSame('Validation Error', $error->title);
        $this->assertSame(422, $error->status);
        $this->assertSame('/v2/shipments/req_abc', $error->instance);
        $this->assertTrue($error->hasFieldErrors());
        $this->assertSame($fieldError, $error->fieldErrors[0]);
    }

    public function testFieldErrorHoldsAllFields(): void
    {
        $fieldError = new FieldError('bad', '/a', 'b', 'INVALID_FORMAT');

        $this->assertSame('bad', $fieldError->detail);
        $this->assertSame('/a', $fieldError->pointer);
        $this->assertSame('b', $fieldError->parameter);
        $this->assertSame('INVALID_FORMAT', $fieldError->code);
    }
}
