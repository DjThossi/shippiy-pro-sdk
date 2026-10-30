<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\RateLimit;
use PHPUnit\Framework\TestCase;

final class RateLimitTest extends TestCase
{
    public function testStoresValues(): void
    {
        $rateLimit = new RateLimit(100, 42, 1776685072);

        $this->assertSame(100, $rateLimit->limit);
        $this->assertSame(42, $rateLimit->remaining);
        $this->assertSame(1776685072, $rateLimit->reset);
    }

    public function testHasDataIsTrueWhenAnyValueIsPresent(): void
    {
        $this->assertTrue((new RateLimit(100, null, null))->hasData());
        $this->assertTrue((new RateLimit(null, 42, null))->hasData());
        $this->assertTrue((new RateLimit(null, null, 1))->hasData());
    }

    public function testHasDataIsFalseWhenAllValuesAreNull(): void
    {
        $this->assertFalse((new RateLimit(null, null, null))->hasData());
    }
}
