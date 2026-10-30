<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\LabelFormatting;
use DjThossi\ShippyProSdk\Domain\LabelType;
use PHPUnit\Framework\TestCase;

final class LabelFormattingTest extends TestCase
{
    public function testEmptyByDefault(): void
    {
        $formatting = new LabelFormatting();

        $this->assertTrue($formatting->isEmpty());
        $this->assertNull($formatting->labelType);
        $this->assertNull($formatting->labelWidthCm);
        $this->assertNull($formatting->labelHeightCm);
        $this->assertNull($formatting->rotate);
        $this->assertNull($formatting->pageNumber);
        $this->assertNull($formatting->splitPages);
    }

    public function testStoresValues(): void
    {
        // A 4x8 inch label expressed in centimetres.
        $formatting = new LabelFormatting(LabelType::Pdf, 10.16, 20.32, 90, 1, true);

        $this->assertFalse($formatting->isEmpty());
        $this->assertSame(LabelType::Pdf, $formatting->labelType);
        $this->assertSame(10.16, $formatting->labelWidthCm);
        $this->assertSame(20.32, $formatting->labelHeightCm);
        $this->assertSame(90, $formatting->rotate);
        $this->assertSame(1, $formatting->pageNumber);
        $this->assertTrue($formatting->splitPages);
    }

    public function testZplType(): void
    {
        $formatting = new LabelFormatting(LabelType::Zpl);

        $this->assertSame(LabelType::Zpl, $formatting->labelType);
        $this->assertFalse($formatting->isEmpty());
    }
}
