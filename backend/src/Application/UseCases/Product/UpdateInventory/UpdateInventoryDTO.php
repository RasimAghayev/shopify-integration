<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\UpdateInventory;

final readonly class UpdateInventoryDTO
{
    public function __construct(
        public string $sku,
        public int $quantity,
        public ?string $reason = null,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sku: (string) ($data['sku'] ?? ''),
            quantity: (int) ($data['quantity'] ?? 0),
            reason: $data['reason'] ?? null,
        );
    }
}
