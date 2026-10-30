<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Tests\Unit\Domain;

use DjThossi\ShippyProSdk\Domain\MoneyAmount;
use DjThossi\ShippyProSdk\Exception\InvalidMoneyAmountException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyAmountTest extends TestCase
{
    public function testIntegerAmountRendersApiString(): void
    {
        $money = new MoneyAmount('123', 'EUR');

        $this->assertSame('123', $money->amount);
        $this->assertSame('EUR', $money->currency);
        $this->assertSame('123 EUR', $money->toApiString());
    }

    public function testDecimalWithDotIsAccepted(): void
    {
        $money = new MoneyAmount('10.50', 'usd');

        $this->assertSame('10.50', $money->amount);
        $this->assertSame('USD', $money->currency);
        $this->assertSame('10.50 USD', $money->toApiString());
    }

    public function testCommaDecimalSeparatorIsRejected(): void
    {
        $this->expectException(InvalidMoneyAmountException::class);
        $this->expectExceptionMessage('MoneyAmount must be a decimal using "." as the only decimal separator');

        new MoneyAmount('10,50', 'EUR');
    }

    #[DataProvider('invalidAmountProvider')]
    public function testInvalidAmountThrows(string $amount): void
    {
        $this->expectException(InvalidMoneyAmountException::class);

        new MoneyAmount($amount, 'EUR');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidAmountProvider(): array
    {
        return [
            'empty' => [''],
            'letters' => ['abc'],
            'thousands comma' => ['1,000'],
            'trailing dot' => ['12.'],
            'leading dot' => ['.5'],
            'currency suffix' => ['12 EUR'],
        ];
    }

    public function testEmptyCurrencyIsRejected(): void
    {
        $this->expectException(InvalidMoneyAmountException::class);
        $this->expectExceptionMessage('MoneyAmount currency must not be empty');

        new MoneyAmount('12', '   ');
    }
}
