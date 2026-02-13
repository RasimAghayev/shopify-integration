<?php

declare(strict_types=1);

namespace Src\Infrastructure\Mappers;

use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Entities\ProductVariant;

final readonly class ProductMapper
{
    /**
     * Convert Product entity to array for serialization
     *
     * @return array<string, mixed>
     */
    public function toArray(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku->value,
            'title' => $product->title,
            'description' => $product->description,
            'price' => $product->price->amount,
            'currency' => $product->price->currency->value,
            'status' => $product->status->value,
            'inventory_quantity' => $product->inventoryQuantity,
            'shopify_id' => $product->shopifyId,
            'variants' => array_map(
                fn (ProductVariant $v) => $this->variantToArray($v),
                $product->variants
            ),
            'created_at' => $product->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $product->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert ProductVariant entity to array
     *
     * @return array<string, mixed>
     */
    public function variantToArray(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku->value,
            'price' => $variant->price->amount,
            'currency' => $variant->price->currency->value,
            'inventory_quantity' => $variant->inventoryQuantity,
            'shopify_variant_id' => $variant->shopifyVariantId,
            'weight' => $variant->weight,
            'weight_unit' => $variant->weightUnit,
        ];
    }

    /**
     * Convert to persistence model attributes (for database operations)
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(Product $product): array
    {
        return [
            'title' => $product->title,
            'description' => $product->description,
            'price' => $product->price->amount,
            'currency' => $product->price->currency->value,
            'status' => $product->status->value,
            'inventory_quantity' => $product->inventoryQuantity,
            'shopify_id' => $product->shopifyId,
        ];
    }

    /**
     * Convert variant to persistence model attributes
     *
     * @return array<string, mixed>
     */
    public function variantToPersistenceArray(ProductVariant $variant): array
    {
        return [
            'price' => $variant->price->amount,
            'currency' => $variant->price->currency->value,
            'inventory_quantity' => $variant->inventoryQuantity,
            'shopify_variant_id' => $variant->shopifyVariantId,
            'weight' => $variant->weight,
            'weight_unit' => $variant->weightUnit,
        ];
    }
}
