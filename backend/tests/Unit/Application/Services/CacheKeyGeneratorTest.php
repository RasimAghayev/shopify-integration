<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Services;

use PHPUnit\Framework\TestCase;
use Src\Application\Services\CacheKeyGenerator;

final class CacheKeyGeneratorTest extends TestCase
{
    private CacheKeyGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CacheKeyGenerator();
    }

    /** @test */
    public function it_generates_products_list_key(): void
    {
        $key = $this->generator->productsListKey(1, 10);
        $this->assertEquals('products_json_1_10', $key);

        $key = $this->generator->productsListKey(5, 50);
        $this->assertEquals('products_json_5_50', $key);
    }

    /** @test */
    public function it_generates_product_by_sku_key(): void
    {
        $key = $this->generator->productBySkuKey('TEST-001');
        $this->assertEquals('product.sku.TEST-001', $key);
    }

    /** @test */
    public function it_generates_product_by_id_key(): void
    {
        $key = $this->generator->productByIdKey(123);
        $this->assertEquals('product.id.123', $key);
    }

    /** @test */
    public function it_generates_product_by_shopify_id_key(): void
    {
        $key = $this->generator->productByShopifyIdKey('shopify_123456');
        $this->assertEquals('product.shopify.shopify_123456', $key);
    }

    /** @test */
    public function it_returns_products_tag(): void
    {
        $tag = $this->generator->getProductsTag();
        $this->assertEquals('products', $tag);
        $this->assertEquals(CacheKeyGenerator::PRODUCTS_TAG, $tag);
    }
}
