<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Mappers;

use PHPUnit\Framework\TestCase;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Entities\ProductVariant;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\ProductStatus;
use Src\Domain\Product\ValueObjects\Sku;
use Src\Infrastructure\Mappers\ProductMapper;

final class ProductMapperTest extends TestCase
{
    private ProductMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new ProductMapper();
    }

    /** @test */
    public function it_converts_product_to_array(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            description: 'Description',
            status: ProductStatus::ACTIVE,
            shopifyId: '123456',
            inventoryQuantity: 100,
        );

        $array = $this->mapper->toArray($product);

        $this->assertEquals('TEST-001', $array['sku']);
        $this->assertEquals('Test Product', $array['title']);
        $this->assertEquals(1999, $array['price']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals('Description', $array['description']);
        $this->assertEquals('active', $array['status']);
        $this->assertEquals('123456', $array['shopify_id']);
        $this->assertEquals(100, $array['inventory_quantity']);
    }

    /** @test */
    public function it_converts_variant_to_array(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
            shopifyVariantId: '111222333',
            weight: 0.5,
            weightUnit: 'kg',
        );

        $array = $this->mapper->variantToArray($variant);

        $this->assertEquals('TEST-001-S', $array['sku']);
        $this->assertEquals(1999, $array['price']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals(50, $array['inventory_quantity']);
        $this->assertEquals('111222333', $array['shopify_variant_id']);
        $this->assertEquals(0.5, $array['weight']);
        $this->assertEquals('kg', $array['weight_unit']);
    }

    /** @test */
    public function it_converts_product_to_persistence_array(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(2999, Currency::EUR),
            description: 'Desc',
            status: ProductStatus::DRAFT,
            shopifyId: '789',
            inventoryQuantity: 25,
        );

        $array = $this->mapper->toPersistenceArray($product);

        $this->assertEquals('Test Product', $array['title']);
        $this->assertEquals('Desc', $array['description']);
        $this->assertEquals(2999, $array['price']);
        $this->assertEquals('EUR', $array['currency']);
        $this->assertEquals('draft', $array['status']);
        $this->assertEquals('789', $array['shopify_id']);
        $this->assertEquals(25, $array['inventory_quantity']);
        // SKU should not be in persistence array (it's used as identifier)
        $this->assertArrayNotHasKey('sku', $array);
    }

    /** @test */
    public function it_handles_null_optional_fields(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $array = $this->mapper->toArray($product);

        $this->assertNull($array['description']);
        $this->assertNull($array['shopify_id']);
    }

    /** @test */
    public function it_includes_variants_in_product_array(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 25,
        );

        $productWithVariant = $product->addVariant($variant);
        $array = $this->mapper->toArray($productWithVariant);

        $this->assertCount(1, $array['variants']);
        $this->assertEquals('TEST-001-S', $array['variants'][0]['sku']);
    }

    /** @test */
    public function it_formats_dates_correctly(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $array = $this->mapper->toArray($product);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $array['created_at']
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $array['updated_at']
        );
    }
}
