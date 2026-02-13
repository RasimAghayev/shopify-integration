<?php

declare(strict_types=1);

namespace Src\Application\Services;

final readonly class CacheKeyGenerator
{
    private const string PRODUCTS_LIST_PREFIX = 'products_json';
    private const string PRODUCT_SKU_PREFIX = 'product.sku';
    private const string PRODUCT_ID_PREFIX = 'product.id';
    private const string PRODUCT_SHOPIFY_PREFIX = 'product.shopify';

    public const string PRODUCTS_TAG = 'products';

    public function productsListKey(int $page, int $perPage): string
    {
        return sprintf('%s_%d_%d', self::PRODUCTS_LIST_PREFIX, $page, $perPage);
    }

    public function productBySkuKey(string $sku): string
    {
        return sprintf('%s.%s', self::PRODUCT_SKU_PREFIX, $sku);
    }

    public function productByIdKey(int $id): string
    {
        return sprintf('%s.%d', self::PRODUCT_ID_PREFIX, $id);
    }

    public function productByShopifyIdKey(string $shopifyId): string
    {
        return sprintf('%s.%s', self::PRODUCT_SHOPIFY_PREFIX, $shopifyId);
    }

    public function getProductsTag(): string
    {
        return self::PRODUCTS_TAG;
    }
}
