<?php

declare(strict_types=1);

namespace Src\Domain\Product\Exceptions;

use InvalidArgumentException;

final class InvalidSkuException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('SKU cannot be empty');
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("SKU cannot exceed {$maxLength} characters");
    }

    public static function invalidCharacters(): self
    {
        return new self('SKU contains invalid characters');
    }
}
