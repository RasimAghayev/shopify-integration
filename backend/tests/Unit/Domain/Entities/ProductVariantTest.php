<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Domain\Product\Entities\ProductVariant;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\Sku;

final class ProductVariantTest extends TestCase
{
    /** @test */
    public function it_creates_variant_with_required_fields(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
        );

        $this->assertEquals('TEST-001-S', $variant->sku->value);
        $this->assertEquals(1999, $variant->price->amount);
        $this->assertEquals(50, $variant->inventoryQuantity);
    }

    /** @test */
    public function it_creates_variant_with_all_fields(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
            shopifyVariantId: '111222333',
            weight: 0.5,
            weightUnit: 'kg',
        );

        $this->assertEquals('111222333', $variant->shopifyVariantId);
        $this->assertEquals(0.5, $variant->weight);
        $this->assertEquals('kg', $variant->weightUnit);
    }

    /** @test */
    public function it_creates_from_shopify_data(): void
    {
        $data = [
            'id' => '111222333',
            'sku' => 'TEST-001-S',
            'price' => '19.99',
            'inventory_quantity' => 50,
            'weight' => 0.5,
            'weight_unit' => 'kg',
        ];

        $variant = ProductVariant::fromShopifyData($data);

        $this->assertEquals('TEST-001-S', $variant->sku->value);
        $this->assertEquals(1999, $variant->price->amount);
        $this->assertEquals(50, $variant->inventoryQuantity);
        $this->assertEquals('111222333', $variant->shopifyVariantId);
    }

    /** @test */
    public function it_throws_exception_for_negative_inventory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inventory quantity cannot be negative');

        ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: -10,
        );
    }

    /** @test */
    public function it_throws_exception_for_negative_weight(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Weight cannot be negative');

        ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
            weight: -1.0,
        );
    }

    /** @test */
    public function it_updates_price(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
        );

        $updated = $variant->withPrice(new Price(2999, Currency::USD));

        $this->assertEquals(2999, $updated->price->amount);
        $this->assertEquals(1999, $variant->price->amount);
    }

    /** @test */
    public function it_updates_inventory(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
        );

        $updated = $variant->withInventoryQuantity(100);

        $this->assertEquals(100, $updated->inventoryQuantity);
        $this->assertEquals(50, $variant->inventoryQuantity);
    }

    /** @test */
    public function it_checks_if_in_stock(): void
    {
        $inStock = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
        );

        $outOfStock = ProductVariant::create(
            sku: new Sku('TEST-001-M'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 0,
        );

        $this->assertTrue($inStock->isInStock());
        $this->assertFalse($outOfStock->isInStock());
    }

    /** @test */
    public function it_converts_to_array(): void
    {
        $variant = ProductVariant::create(
            sku: new Sku('TEST-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 50,
            shopifyVariantId: '111222333',
            weight: 0.5,
            weightUnit: 'kg',
        );

        $array = $variant->toArray();

        $this->assertEquals('TEST-001-S', $array['sku']);
        $this->assertEquals(1999, $array['price']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals(50, $array['inventory_quantity']);
        $this->assertEquals('111222333', $array['shopify_variant_id']);
        $this->assertEquals(0.5, $array['weight']);
        $this->assertEquals('kg', $array['weight_unit']);
    }
}
