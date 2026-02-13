<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Entities\ProductVariant;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\ProductStatus;
use Src\Domain\Product\ValueObjects\Sku;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;
use Src\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Tests\TestCase;

final class EloquentProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentProductRepository();
    }

    /** @test */
    public function it_saves_new_product(): void
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

        $saved = $this->repository->save($product);

        $this->assertNotNull($saved->id);
        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'title' => 'Test Product',
            'shopify_id' => '123456',
        ]);
    }

    /** @test */
    public function it_updates_existing_product(): void
    {
        $product = Product::create(
            sku: new Sku('TEST-002'),
            title: 'Original Title',
            price: new Price(1999, Currency::USD),
        );

        $this->repository->save($product);

        $updated = $product->withTitle('Updated Title');
        $this->repository->save($updated);

        $this->assertEquals(1, ProductModel::where('sku', 'TEST-002')->count());

        $retrieved = $this->repository->findBySku(new Sku('TEST-002'));
        $this->assertEquals('Updated Title', $retrieved->title);
    }

    /** @test */
    public function it_finds_by_sku(): void
    {
        ProductModel::factory()->create([
            'sku' => 'FIND-SKU',
            'title' => 'Found Product',
        ]);

        $product = $this->repository->findBySku(new Sku('FIND-SKU'));

        $this->assertNotNull($product);
        $this->assertEquals('FIND-SKU', $product->sku->value);
        $this->assertEquals('Found Product', $product->title);
    }

    /** @test */
    public function it_finds_by_shopify_id(): void
    {
        ProductModel::factory()->create([
            'sku' => 'SHOPIFY-FIND',
            'shopify_id' => 'shopify_123',
        ]);

        $product = $this->repository->findByShopifyId('shopify_123');

        $this->assertNotNull($product);
        $this->assertEquals('shopify_123', $product->shopifyId);
    }

    /** @test */
    public function it_finds_by_id(): void
    {
        $model = ProductModel::factory()->create(['sku' => 'ID-FIND']);

        $product = $this->repository->findById($model->id);

        $this->assertNotNull($product);
        $this->assertEquals($model->id, $product->id);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_product(): void
    {
        $product = $this->repository->findBySku(new Sku('NONEXISTENT'));

        $this->assertNull($product);
    }

    /** @test */
    public function it_paginates_find_all(): void
    {
        ProductModel::factory()->count(25)->create();

        $result = $this->repository->findAll(page: 1, perPage: 10);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(10, $result['data']);
        $this->assertEquals(25, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['page']);
        $this->assertEquals(10, $result['meta']['per_page']);
    }

    /** @test */
    public function it_deletes_by_sku(): void
    {
        ProductModel::factory()->create(['sku' => 'DELETE-ME']);

        $this->repository->delete(new Sku('DELETE-ME'));

        $this->assertDatabaseMissing('products', ['sku' => 'DELETE-ME']);
    }

    /** @test */
    public function it_deletes_by_id(): void
    {
        $model = ProductModel::factory()->create(['sku' => 'DELETE-BY-ID']);

        $this->repository->deleteById($model->id);

        $this->assertDatabaseMissing('products', ['id' => $model->id]);
    }

    /** @test */
    public function it_checks_existence_by_sku(): void
    {
        ProductModel::factory()->create(['sku' => 'EXISTS-SKU']);

        $this->assertTrue($this->repository->existsBySku(new Sku('EXISTS-SKU')));
        $this->assertFalse($this->repository->existsBySku(new Sku('NOT-EXISTS')));
    }

    /** @test */
    public function it_checks_existence_by_shopify_id(): void
    {
        ProductModel::factory()->create(['shopify_id' => 'shopify_exists']);

        $this->assertTrue($this->repository->existsByShopifyId('shopify_exists'));
        $this->assertFalse($this->repository->existsByShopifyId('shopify_not_exists'));
    }

    /** @test */
    public function it_counts_products(): void
    {
        ProductModel::factory()->count(5)->create();

        $count = $this->repository->count();

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function it_saves_product_with_variants(): void
    {
        $product = Product::create(
            sku: new Sku('MAIN-001'),
            title: 'Product with Variants',
            price: new Price(1999, Currency::USD),
        );

        $variant = ProductVariant::create(
            sku: new Sku('MAIN-001-S'),
            price: new Price(1999, Currency::USD),
            inventoryQuantity: 25,
        );

        $productWithVariant = $product->addVariant($variant);
        $saved = $this->repository->save($productWithVariant);

        $this->assertCount(1, $saved->variants);
        $this->assertEquals('MAIN-001-S', $saved->variants[0]->sku->value);
    }

    /** @test */
    public function it_preserves_product_id_on_update(): void
    {
        $product = Product::create(
            sku: new Sku('PRESERVE-ID'),
            title: 'Original',
            price: new Price(1999, Currency::USD),
        );

        $saved = $this->repository->save($product);
        $originalId = $saved->id;

        $updated = $saved->withTitle('Updated');
        $savedAgain = $this->repository->save($updated);

        $this->assertEquals($originalId, $savedAgain->id);
    }
}
