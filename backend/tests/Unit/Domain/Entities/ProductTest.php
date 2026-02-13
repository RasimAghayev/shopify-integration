<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Entities\ProductVariant;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\ProductStatus;
use Src\Domain\Product\ValueObjects\Sku;

final class ProductTest extends TestCase
{
    /** @test */
    public function it_creates_product_with_required_fields(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $this->assertEquals('TEST-001', $product->sku->value);
        $this->assertEquals('Test Product', $product->title);
        $this->assertEquals(1999, $product->price->amount);
        $this->assertEquals(ProductStatus::DRAFT, $product->status);
    }

    /** @test */
    public function it_creates_product_with_all_fields(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            description: 'Product description',
            status: ProductStatus::ACTIVE,
            shopifyId: '123456789',
            inventoryQuantity: 100,
        );

        $this->assertEquals('Product description', $product->description);
        $this->assertEquals(ProductStatus::ACTIVE, $product->status);
        $this->assertEquals('123456789', $product->shopifyId);
        $this->assertEquals(100, $product->inventoryQuantity);
    }

    /** @test */
    public function it_creates_product_from_shopify_data(): void
    {
        $shopifyData = [
            'id' => '123456789',
            'title' => 'Shopify Product',
            'body_html' => '<p>Description</p>',
            'status' => 'active',
            'variants' => [
                [
                    'id' => '111',
                    'sku' => 'TEST-001',
                    'price' => '19.99',
                    'inventory_quantity' => 50,
                ],
            ],
        ];

        $product = Product::fromShopifyData($shopifyData);

        $this->assertEquals('TEST-001', $product->sku->value);
        $this->assertEquals('Shopify Product', $product->title);
        $this->assertEquals('123456789', $product->shopifyId);
        $this->assertEquals(ProductStatus::ACTIVE, $product->status);
        $this->assertEquals(1999, $product->price->amount);
    }

    /** @test */
    public function it_throws_exception_for_missing_variants(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product must have at least one variant');

        Product::fromShopifyData([
            'id' => '123',
            'title' => 'Test',
            'variants' => [],
        ]);
    }

    /** @test */
    public function it_throws_exception_for_missing_sku(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Product::fromShopifyData([
            'id' => '123',
            'title' => 'Test',
            'variants' => [
                ['id' => '111', 'price' => '19.99'],
            ],
        ]);
    }

    /** @test */
    public function it_updates_title(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Original Title',
            price: new Price(1999, Currency::USD),
        );

        $updated = $product->withTitle('Updated Title');

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('Original Title', $product->title);
    }

    /** @test */
    public function it_updates_price(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $updated = $product->withPrice(new Price(2999, Currency::USD));

        $this->assertEquals(2999, $updated->price->amount);
        $this->assertEquals(1999, $product->price->amount);
    }

    /** @test */
    public function it_updates_status(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $updated = $product->withStatus(ProductStatus::ACTIVE);

        $this->assertEquals(ProductStatus::ACTIVE, $updated->status);
        $this->assertEquals(ProductStatus::DRAFT, $product->status);
    }

    /** @test */
    public function it_updates_inventory(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 100,
        );

        $updated = $product->withInventoryQuantity(50);

        $this->assertEquals(50, $updated->inventoryQuantity);
        $this->assertEquals(100, $product->inventoryQuantity);
    }

    /** @test */
    public function it_throws_exception_for_negative_inventory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inventory quantity cannot be negative');

        Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            inventoryQuantity: -10,
        );
    }

    /** @test */
    public function it_checks_if_in_stock(): void
    {
        $inStock = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 10,
        );

        $outOfStock = Product::create(
            sku: new Sku('TEST-002'),
            title: 'Test Product 2',
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 0,
        );

        $this->assertTrue($inStock->isInStock());
        $this->assertFalse($outOfStock->isInStock());
    }

    /** @test */
    public function it_checks_if_active(): void
    {
        $active = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
            status: ProductStatus::ACTIVE,
        );

        $draft = Product::create(
            sku: new Sku('TEST-002'),
            title: 'Test Product 2',
            price: new Price(1999, Currency::USD),
            status: ProductStatus::DRAFT,
        );

        $this->assertTrue($active->isActive());
        $this->assertFalse($draft->isActive());
    }

    /** @test */
    public function it_adds_variant(): void
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

        $updated = $product->addVariant($variant);

        $this->assertCount(1, $updated->variants);
        $this->assertCount(0, $product->variants);
    }

    /** @test */
    public function it_converts_to_array(): void
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

        $array = $product->toArray();

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
    public function it_has_timestamps(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-001'),
            title: 'Test Product',
            price: new Price(1999, Currency::USD),
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $product->createdAt);
        $this->assertInstanceOf(\DateTimeImmutable::class, $product->updatedAt);
    }
}
