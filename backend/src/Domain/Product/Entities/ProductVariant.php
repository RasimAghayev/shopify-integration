<?php

declare(strict_types=1);

namespace Src\Domain\Product\Entities;

use InvalidArgumentException;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\Sku;

final readonly class ProductVariant
{
    private function __construct(
        public Sku $sku,
        public Price $price,
        public int $inventoryQuantity,
        public ?int $id = null,
        public ?string $shopifyVariantId = null,
        public ?float $weight = null,
        public ?string $weightUnit = null,
    ) {
        if ($inventoryQuantity < 0) {
            throw new InvalidArgumentException('Inventory quantity cannot be negative');
        }

        if ($weight !== null && $weight < 0) {
            throw new InvalidArgumentException('Weight cannot be negative');
        }
    }

    public static function create(
        Sku $sku,
        Price $price,
        int $inventoryQuantity,
        ?int $id = null,
        ?string $shopifyVariantId = null,
        ?float $weight = null,
        ?string $weightUnit = null,
    ): self {
        return new self(
            sku: $sku,
            price: $price,
            inventoryQuantity: $inventoryQuantity,
            id: $id,
            shopifyVariantId: $shopifyVariantId,
            weight: $weight,
            weightUnit: $weightUnit,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromShopifyData(array $data): self
    {
        $sku = $data['sku'] ?? null;
        if (empty($sku)) {
            throw new InvalidArgumentException('Variant SKU is required');
        }

        $price = isset($data['price'])
            ? Price::fromDecimal((float) $data['price'], Currency::USD)
            : Price::zero(Currency::USD);

        return new self(
            sku: new Sku($sku),
            price: $price,
            inventoryQuantity: (int) ($data['inventory_quantity'] ?? 0),
            shopifyVariantId: isset($data['id']) ? (string) $data['id'] : null,
            weight: isset($data['weight']) ? (float) $data['weight'] : null,
            weightUnit: $data['weight_unit'] ?? null,
        );
    }

    /**
     * Creates a new instance with specified property changes (Wither pattern)
     *
     * @param  array<string, mixed>  $changes
     */
    private function with(array $changes): self
    {
        $inventoryQuantity = $changes['inventoryQuantity'] ?? $this->inventoryQuantity;
        if ($inventoryQuantity < 0) {
            throw new InvalidArgumentException('Inventory quantity cannot be negative');
        }

        return new self(
            sku: $this->sku,
            price: $changes['price'] ?? $this->price,
            inventoryQuantity: $inventoryQuantity,
            id: $this->id,
            shopifyVariantId: $this->shopifyVariantId,
            weight: $changes['weight'] ?? $this->weight,
            weightUnit: $changes['weightUnit'] ?? $this->weightUnit,
        );
    }

    public function withPrice(Price $price): self
    {
        return $this->with(['price' => $price]);
    }

    public function withInventoryQuantity(int $quantity): self
    {
        return $this->with(['inventoryQuantity' => $quantity]);
    }

    public function isInStock(): bool
    {
        return $this->inventoryQuantity > 0;
    }
}
