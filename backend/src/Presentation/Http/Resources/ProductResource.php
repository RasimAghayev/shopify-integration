<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Product\Entities\Product;

/**
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => $product->id,
            'sku' => $product->sku->value,
            'title' => $product->title,
            'description' => $product->description,
            'status' => $product->status->value,
            'price' => [
                'amount' => $product->price->amount,
                'formatted' => $product->price->format(),
                'currency' => $product->price->currency->value,
            ],
            'inventory' => $product->inventoryQuantity,
            'inStock' => $product->isInStock(),
            'shopifyId' => $product->shopifyId,
            'variants' => array_map(fn ($v) => [
                'id' => $v->id,
                'sku' => $v->sku->value,
                'price' => [
                    'amount' => $v->price->amount,
                    'formatted' => $v->price->format(),
                ],
                'inventory' => $v->inventoryQuantity,
            ], $product->variants),
            'createdAt' => $product->createdAt->format('c'),
            'updatedAt' => $product->updatedAt->format('c'),
        ];
    }
}
