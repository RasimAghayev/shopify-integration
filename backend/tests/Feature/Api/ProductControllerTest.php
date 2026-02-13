<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_products_with_pagination(): void
    {
        ProductModel::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/products?page=1&per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'sku', 'title', 'status', 'price', 'inventory'],
                ],
                'meta' => ['page', 'per_page', 'total'],
            ])
            ->assertJsonCount(10, 'data');
    }

    /** @test */
    public function it_lists_products_second_page(): void
    {
        ProductModel::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/products?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_retrieves_single_product_by_sku(): void
    {
        $product = ProductModel::factory()->create([
            'sku' => 'TEST-001',
            'title' => 'Test Product',
        ]);

        $response = $this->getJson('/api/v1/products/TEST-001');

        $response->assertOk()
            ->assertJsonPath('data.sku', 'TEST-001')
            ->assertJsonPath('data.title', 'Test Product');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/v1/products/NONEXISTENT-SKU');

        $response->assertNotFound()
            ->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_deletes_product(): void
    {
        ProductModel::factory()->create(['sku' => 'DELETE-ME']);

        $response = $this->deleteJson('/api/v1/products/DELETE-ME');

        $response->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully');

        $this->assertDatabaseMissing('products', ['sku' => 'DELETE-ME']);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_product(): void
    {
        $response = $this->deleteJson('/api/v1/products/NONEXISTENT');

        $response->assertNotFound();
    }

    /** @test */
    public function it_returns_products_with_correct_price_structure(): void
    {
        ProductModel::factory()->create([
            'sku' => 'PRICE-TEST',
            'price' => 1999,
            'currency' => 'USD',
        ]);

        $response = $this->getJson('/api/v1/products/PRICE-TEST');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'price' => ['amount', 'formatted', 'currency'],
                ],
            ])
            ->assertJsonPath('data.price.amount', 1999)
            ->assertJsonPath('data.price.currency', 'USD');
    }

    /** @test */
    public function it_returns_empty_list_when_no_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
