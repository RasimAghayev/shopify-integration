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

    public function withPrice(Price $price): self
    {
        return new self(
            sku: $this->sku,
            price: $price,
            inventoryQuantity: $this->inventoryQuantity,
            id: $this->id,
            shopifyVariantId: $this->shopifyVariantId,
            weight: $this->weight,
            weightUnit: $this->weightUnit,
        );
    }

    public function withInventoryQuantity(int $quantity): self
    {
        return new self(
            sku: $this->sku,
            price: $this->price,
            inventoryQuantity: $quantity,
            id: $this->id,
            shopifyVariantId: $this->shopifyVariantId,
            weight: $this->weight,
            weightUnit: $this->weightUnit,
        );
    }

    public function isInStock(): bool
    {
        return $this->inventoryQuantity > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku->value,
            'price' => $this->price->amount,
            'currency' => $this->price->currency->value,
            'inventory_quantity' => $this->inventoryQuantity,
            'shopify_variant_id' => $this->shopifyVariantId,
            'weight' => $this->weight,
            'weight_unit' => $this->weightUnit,
        ];
    }
}
