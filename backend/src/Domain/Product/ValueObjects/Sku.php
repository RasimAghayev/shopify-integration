<?php

declare(strict_types=1);

namespace Src\Domain\Product\ValueObjects;

use Src\Domain\Product\Exceptions\InvalidSkuException;

final readonly class Sku
{
    private const int MAX_LENGTH = 50;

    private const string VALID_PATTERN = '/^[A-Z0-9\-_]+$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = $this->normalize($value);
        $this->validate($normalized);
        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function normalize(string $value): string
    {
        return strtoupper(trim($value));
    }

    private function validate(string $value): void
    {
        if ($value === '') {
            throw InvalidSkuException::empty();
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw InvalidSkuException::tooLong(self::MAX_LENGTH);
        }

        if (! preg_match(self::VALID_PATTERN, $value)) {
            throw InvalidSkuException::invalidCharacters();
        }
    }
}
