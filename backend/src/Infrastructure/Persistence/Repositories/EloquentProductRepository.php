<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Repositories;

use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Entities\ProductVariant;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\ProductStatus;
use Src\Domain\Product\ValueObjects\Sku;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;
use Src\Infrastructure\Persistence\Eloquent\ProductVariantModel;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): Product
    {
        // Use shopify_id for matching if available, otherwise use sku
        $matchCriteria = $product->shopifyId !== null
            ? ['shopify_id' => $product->shopifyId]
            : ['sku' => $product->sku->value];

        $model = ProductModel::updateOrCreate(
            $matchCriteria,
            [
                'sku' => $product->sku->value,
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price->amount,
                'currency' => $product->price->currency->value,
                'status' => $product->status->value,
                'inventory_quantity' => $product->inventoryQuantity,
                'shopify_id' => $product->shopifyId,
            ],
        );

        // Save variants
        foreach ($product->variants as $variant) {
            ProductVariantModel::updateOrCreate(
                [
                    'product_id' => $model->id,
                    'sku' => $variant->sku->value,
                ],
                [
                    'price' => $variant->price->amount,
                    'currency' => $variant->price->currency->value,
                    'inventory_quantity' => $variant->inventoryQuantity,
                    'shopify_variant_id' => $variant->shopifyVariantId,
                    'weight' => $variant->weight,
                    'weight_unit' => $variant->weightUnit,
                ],
            );
        }

        return $this->toEntity($model->fresh(['variants']));
    }

    public function findBySku(Sku $sku): ?Product
    {
        $model = ProductModel::with('variants')
            ->where('sku', $sku->value)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByShopifyId(string $shopifyId): ?Product
    {
        $model = ProductModel::with('variants')
            ->where('shopify_id', $shopifyId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::with('variants')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    /**
     * @return array{data: array<Product>, total: int, page: int, per_page: int}
     */
    public function findAll(int $page = 1, int $perPage = 10): array
    {
        $paginator = ProductModel::with('variants')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $products = collect($paginator->items())
            ->map(fn (ProductModel $model) => $this->toEntity($model))
            ->all();

        return [
            'data' => $products,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public function delete(Sku $sku): void
    {
        ProductModel::where('sku', $sku->value)->delete();
    }

    public function deleteById(int $id): void
    {
        ProductModel::destroy($id);
    }

    public function existsBySku(Sku $sku): bool
    {
        return ProductModel::where('sku', $sku->value)->exists();
    }

    public function existsByShopifyId(string $shopifyId): bool
    {
        return ProductModel::where('shopify_id', $shopifyId)->exists();
    }

    public function count(): int
    {
        return ProductModel::count();
    }

    private function toEntity(ProductModel $model): Product
    {
        $variants = $model->variants->map(function (ProductVariantModel $variantModel) {
            return ProductVariant::create(
                sku: new Sku($variantModel->sku),
                price: new Price($variantModel->price, Currency::from($variantModel->currency)),
                inventoryQuantity: $variantModel->inventory_quantity,
                id: $variantModel->id,
                shopifyVariantId: $variantModel->shopify_variant_id,
                weight: $variantModel->weight,
                weightUnit: $variantModel->weight_unit,
            );
        })->all();

        return Product::create(
            sku: new Sku($model->sku),
            title: $model->title,
            price: new Price($model->price, Currency::from($model->currency)),
            description: $model->description,
            status: ProductStatus::from($model->status),
            shopifyId: $model->shopify_id,
            inventoryQuantity: $model->inventory_quantity,
            id: $model->id,
        );
    }
}
