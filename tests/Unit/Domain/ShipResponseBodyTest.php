<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\LabelUrlCollection;
use DjThossi\ShippyProSdk\Domain\ShipResponseBody;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use PHPUnit\Framework\TestCase;

final class ShipResponseBodyTest extends TestCase
{
    public function testOkBodyHasNoBusinessError(): void
    {
        $body = new ShipResponseBody('OK', ShipStatus::Ok, new LabelUrlCollection('u'));

        $this->assertFalse($body->hasBusinessError());
        $this->assertSame('OK', $body->result);
    }

    public function testErrorStatusIsBusinessError(): void
    {
        $body = new ShipResponseBody('OK', ShipStatus::Error, new LabelUrlCollection());

        $this->assertTrue($body->hasBusinessError());
    }

    public function testErrorMessageIsBusinessError(): void
    {
        $body = new ShipResponseBody('OK', ShipStatus::Ok, new LabelUrlCollection(), errorMessage: 'boom');

        $this->assertTrue($body->hasBusinessError());
    }
}
