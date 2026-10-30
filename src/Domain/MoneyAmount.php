<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

use DjThossi\ShippyProSdk\Exception\InvalidMoneyAmountException;

use function preg_match;
use function sprintf;
use function strtoupper;
use function trim;

final readonly class MoneyAmount
{
    public string $amount;
    public string $currency;

    public function __construct(string $amount, string $currency)
    {
        $normalizedAmount = trim($amount);
        if (preg_match('/^\d+(\.\d+)?$/', $normalizedAmount) !== 1) {
            throw new InvalidMoneyAmountException('MoneyAmount must be a decimal using "." as the only decimal separator');
        }

        $normalizedCurrency = strtoupper(trim($currency));
        if ($normalizedCurrency === '') {
            throw new InvalidMoneyAmountException('MoneyAmount currency must not be empty');
        }

        $this->amount = $normalizedAmount;
        $this->currency = $normalizedCurrency;
    }

    public function toApiString(): string
    {
        return sprintf('%s %s', $this->amount, $this->currency);
    }
}
