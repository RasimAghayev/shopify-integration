<?php

declare(strict_types=1);

namespace Src\Domain\Product\ValueObjects;

use Src\Domain\Product\Exceptions\CurrencyMismatchException;
use Src\Domain\Product\Exceptions\InvalidPriceException;

final readonly class Price
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
        if ($amount < 0) {
            throw InvalidPriceException::negative();
        }
    }

    public static function fromDecimal(float $decimal, Currency $currency): self
    {
        return new self((int) round($decimal * 100), $currency);
    }

    public static function zero(Currency $currency): self
    {
        return new self(0, $currency);
    }

    public function toDecimal(): float
    {
        return $this->amount / 100;
    }

    public function format(): string
    {
        return $this->currency->format($this->amount);
    }

    public function isLessThan(self $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amount < $other->amount;
    }

    public function isGreaterThan(self $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function add(self $other): self
    {
        $this->ensureSameCurrency($other, 'add');

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other, 'subtract');

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw InvalidPriceException::negative();
        }

        return new self($result, $this->currency);
    }

    public function multiply(int $quantity): self
    {
        return new self($this->amount * $quantity, $this->currency);
    }

    private function ensureSameCurrency(self $other, ?string $operation = null): void
    {
        if ($this->currency !== $other->currency) {
            throw $operation !== null
                ? CurrencyMismatchException::forOperation($operation, $this->currency, $other->currency)
                : CurrencyMismatchException::forComparison($this->currency, $other->currency);
        }
    }
}
