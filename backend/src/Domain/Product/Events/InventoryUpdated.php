<?php

declare(strict_types=1);

namespace Src\Domain\Product\Events;

use DateTimeImmutable;
use Src\Domain\Product\ValueObjects\Sku;

final readonly class InventoryUpdated
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public Sku $sku,
        public int $previousQuantity,
        public int $newQuantity,
        public ?string $reason = null,
    ) {
        $this->occurredAt = new DateTimeImmutable;
    }

    public function difference(): int
    {
        return $this->newQuantity - $this->previousQuantity;
    }

    public function isIncrease(): bool
    {
        return $this->difference() > 0;
    }

    public function isDecrease(): bool
    {
        return $this->difference() < 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sku' => $this->sku->value,
            'previous_quantity' => $this->previousQuantity,
            'new_quantity' => $this->newQuantity,
            'difference' => $this->difference(),
            'reason' => $this->reason,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
