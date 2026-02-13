<?php

declare(strict_types=1);

namespace Src\Domain\Product\ValueObjects;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case CAD = 'CAD';
    case AUD = 'AUD';

    public static function fromString(string $value): self
    {
        return self::from(strtoupper(trim($value)));
    }

    public function symbol(): string
    {
        return match ($this) {
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::CAD => 'C$',
            self::AUD => 'A$',
        };
    }

    public function format(int $amountInCents): string
    {
        $decimal = $amountInCents / 100;

        return match ($this) {
            self::USD => sprintf('$%.2f', $decimal),
            self::EUR => sprintf('%.2f EUR', $decimal),
            self::GBP => sprintf('£%.2f', $decimal),
            self::CAD => sprintf('C$%.2f', $decimal),
            self::AUD => sprintf('A$%.2f', $decimal),
        };
    }

    /**
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
