<?php

declare(strict_types=1);

namespace Src\Application\Contracts;

interface ShopifyClientInterface
{
    /**
     * Get a single product from Shopify
     *
     * @return array<string, mixed>
     */
    public function getProduct(string $shopifyId): array;

    /**
     * Get multiple products from Shopify with pagination
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(int $page = 1, int $limit = 50): array;

    /**
     * Update a product in Shopify
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateProduct(string $shopifyId, array $data): array;

    /**
     * Get products count from Shopify
     */
    public function getProductsCount(): int;

    /**
     * Update inventory level in Shopify
     *
     * @return array<string, mixed>
     */
    public function updateInventory(string $inventoryItemId, int $quantity): array;
}
