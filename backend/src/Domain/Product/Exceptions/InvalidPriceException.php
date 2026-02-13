<?php

declare(strict_types=1);

namespace Src\Domain\Product\Exceptions;

use InvalidArgumentException;

final class InvalidPriceException extends InvalidArgumentException
{
    public static function negative(): self
    {
        return new self('Price cannot be negative');
    }

    public static function invalidAmount(): self
    {
        return new self('Invalid price amount');
    }
}
