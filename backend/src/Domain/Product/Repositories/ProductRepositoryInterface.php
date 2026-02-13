<?php

declare(strict_types=1);

namespace Src\Domain\Product\Repositories;

use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\ValueObjects\Sku;

interface ProductRepositoryInterface
{
    /**
     * Save a product (create or update)
     */
    public function save(Product $product): Product;

    /**
     * Find product by SKU
     */
    public function findBySku(Sku $sku): ?Product;

    /**
     * Find product by Shopify ID
     */
    public function findByShopifyId(string $shopifyId): ?Product;

    /**
     * Find product by internal ID
     */
    public function findById(int $id): ?Product;

    /**
     * Get all products with pagination
     *
     * @return array{data: array<Product>, total: int, page: int, per_page: int}
     */
    public function findAll(int $page = 1, int $perPage = 10): array;

    /**
     * Delete product by SKU
     */
    public function delete(Sku $sku): void;

    /**
     * Delete product by ID
     */
    public function deleteById(int $id): void;

    /**
     * Check if product exists by SKU
     */
    public function existsBySku(Sku $sku): bool;

    /**
     * Check if product exists by Shopify ID
     */
    public function existsByShopifyId(string $shopifyId): bool;

    /**
     * Count all products
     */
    public function count(): int;
}
