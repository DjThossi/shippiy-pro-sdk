<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Property;

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\BaseUrl;
use DjThossi\ShippyProSdk\Domain\RequestTimeout;
use DjThossi\ShippyProSdk\Domain\StatusCode;
use DjThossi\ShippyProSdk\Exception\InvalidApiKeyException;
use DjThossi\ShippyProSdk\Exception\InvalidBaseUrlException;
use DjThossi\ShippyProSdk\Exception\InvalidRequestTimeoutException;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function str_contains;
use function trim;

final class ValueObjectPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Feature: shippy-pro-sdk, Property 4: For any string that is empty or whitespace-only, constructing an ApiKey
     * SHALL throw InvalidApiKeyException; for any non-empty string, construction SHALL succeed and preserve the value.
     */
    #[ErisRepeat(100)]
    public function testApiKeyRejectsEmptyValues(): void
    {
        $this->forAll(Generators::string())
            ->then(function (string $value): void {
                if (trim($value) === '') {
                    $this->expectException(InvalidApiKeyException::class);
                    new ApiKey($value);

                    return;
                }

                $apiKey = new ApiKey($value);
                $this->assertSame($value, $apiKey->value);
            });
    }

    /**
     * Feature: shippy-pro-sdk, Property 5: For any string that is not a valid URL, constructing a BaseUrl SHALL throw
     * InvalidBaseUrlException; for any valid URL, construction SHALL succeed and the stored value SHALL carry no
     * trailing slash.
     */
    #[ErisRepeat(100)]
    public function testBaseUrlValidationInvariant(): void
    {
        $schemes = Generators::elements('http', 'https');
        $hosts = Generators::elements('example.com', 'api.shippypro.com', 'localhost:8080', 'a.b.c');

        $this->forAll($schemes, $hosts, Generators::elements('', '/', '///'))
            ->then(function (string $scheme, string $host, string $trailing): void {
                $url = $scheme . '://' . $host . $trailing;
                $baseUrl = new BaseUrl($url);

                $this->assertSame($scheme . '://' . $host, $baseUrl->value);
                $this->assertStringEndsNotWith('/', $baseUrl->value);
            });
    }

    /**
     * Feature: shippy-pro-sdk, Property 5 (negative): For any clearly invalid URL string, constructing a BaseUrl SHALL
     * throw InvalidBaseUrlException.
     */
    #[ErisRepeat(100)]
    public function testBaseUrlRejectsInvalidUrls(): void
    {
        // Single tokens without a scheme or host separator are never valid URLs.
        $this->forAll(Generators::suchThat(
            static fn (string $value): bool => trim($value, '/') !== '' && !str_contains($value, '://'),
            Generators::string(),
        ))->then(function (string $value): void {
            $this->expectException(InvalidBaseUrlException::class);
            new BaseUrl($value);
        });
    }

    /**
     * Feature: shippy-pro-sdk, Property 6: For any integer n <= 0, constructing a RequestTimeout SHALL throw
     * InvalidRequestTimeoutException; for any integer n > 0, construction SHALL succeed and preserve n.
     */
    #[ErisRepeat(100)]
    public function testRequestTimeoutAcceptsOnlyPositiveSeconds(): void
    {
        $this->forAll(Generators::choose(-1000, 1000))
            ->then(function (int $seconds): void {
                if ($seconds <= 0) {
                    $this->expectException(InvalidRequestTimeoutException::class);
                    new RequestTimeout($seconds);

                    return;
                }

                $timeout = new RequestTimeout($seconds);
                $this->assertSame($seconds, $timeout->seconds);
            });
    }

    /**
     * Feature: shippy-pro-sdk, Property 7: For any HTTP status code integer, exactly the matching StatusCode helper
     * SHALL report true for the codes it names, and isSuccessful SHALL be true exactly for codes in the range 200-299.
     */
    #[ErisRepeat(100)]
    public function testStatusCodeHelperClassification(): void
    {
        $this->forAll(Generators::choose(100, 599))
            ->then(function (int $value): void {
                $statusCode = new StatusCode($value);

                $this->assertSame($value >= 200 && $value < 300, $statusCode->isSuccessful());
                $this->assertSame($value === 200, $statusCode->isOk());
                $this->assertSame($value === 400, $statusCode->isBadRequest());
                $this->assertSame($value === 403, $statusCode->isForbidden());
                $this->assertSame($value === 404, $statusCode->isNotFound());
                $this->assertSame($value === 429, $statusCode->isTooManyRequests());
                $this->assertSame($value === 500, $statusCode->isServerError());
            });
    }
}
