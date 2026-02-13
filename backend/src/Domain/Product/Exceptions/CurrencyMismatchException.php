<?php

declare(strict_types=1);

namespace Src\Domain\Product\Exceptions;

use InvalidArgumentException;
use Src\Domain\Product\ValueObjects\Currency;

final class CurrencyMismatchException extends InvalidArgumentException
{
    public static function forComparison(Currency $currency1, Currency $currency2): self
    {
        return new self(
            "Cannot compare prices with different currencies: {$currency1->value} and {$currency2->value}",
        );
    }

    public static function forOperation(string $operation, Currency $currency1, Currency $currency2): self
    {
        return new self(
            "Cannot {$operation} prices with different currencies: {$currency1->value} and {$currency2->value}",
        );
    }
}
