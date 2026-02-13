<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Src\Application\Contracts\ShopifyClientInterface;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;
use Tests\TestCase;

final class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_syncs_single_product_from_shopify(): void
    {
        $shopifyData = [
            'id' => '123456',
            'title' => 'Shopify Product',
            'body_html' => 'Description',
            'status' => 'active',
            'variants' => [
                [
                    'id' => '111',
                    'sku' => 'SHOPIFY-001',
                    'price' => '29.99',
                    'inventory_quantity' => 100,
                ],
            ],
        ];

        $this->mock(ShopifyClientInterface::class)
            ->shouldReceive('getProduct')
            ->once()
            ->with('123456')
            ->andReturn($shopifyData);

        $response = $this->postJson('/api/v1/sync/product', [
            'shopify_id' => '123456',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sku', 'SHOPIFY-001')
            ->assertJsonPath('data.title', 'Shopify Product')
            ->assertJsonPath('message', 'Product synced successfully');

        $this->assertDatabaseHas('products', [
            'sku' => 'SHOPIFY-001',
            'shopify_id' => '123456',
        ]);
    }

    /** @test */
    public function it_validates_sync_request(): void
    {
        $response = $this->postJson('/api/v1/sync/product', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['shopify_id']);
    }

    /** @test */
    public function it_handles_sync_failure_gracefully(): void
    {
        $this->mock(ShopifyClientInterface::class)
            ->shouldReceive('getProduct')
            ->once()
            ->andThrow(new \RuntimeException('Shopify API Error'));

        $response = $this->postJson('/api/v1/sync/product', [
            'shopify_id' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_updates_existing_product_when_force_is_true(): void
    {
        ProductModel::factory()->create([
            'sku' => 'EXISTING-001',
            'shopify_id' => '123456',
            'title' => 'Old Title',
        ]);

        $shopifyData = [
            'id' => '123456',
            'title' => 'Updated Title',
            'status' => 'active',
            'variants' => [
                [
                    'sku' => 'EXISTING-001',
                    'price' => '39.99',
                    'inventory_quantity' => 50,
                ],
            ],
        ];

        $this->mock(ShopifyClientInterface::class)
            ->shouldReceive('getProduct')
            ->once()
            ->andReturn($shopifyData);

        $response = $this->postJson('/api/v1/sync/product', [
            'shopify_id' => '123456',
            'force_update' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    /** @test */
    public function it_queues_bulk_sync_jobs(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/sync/bulk', [
            'shopify_ids' => ['123', '456', '789'],
        ]);

        $response->assertAccepted()
            ->assertJsonStructure(['message', 'queued_count']);
    }

    /** @test */
    public function it_executes_bulk_sync_immediately(): void
    {
        $this->mock(ShopifyClientInterface::class)
            ->shouldReceive('getProduct')
            ->twice()
            ->andReturn([
                'id' => '123',
                'title' => 'Product',
                'status' => 'active',
                'variants' => [
                    ['sku' => 'BULK-001', 'price' => '19.99', 'inventory_quantity' => 10],
                ],
            ], [
                'id' => '456',
                'title' => 'Product 2',
                'status' => 'active',
                'variants' => [
                    ['sku' => 'BULK-002', 'price' => '29.99', 'inventory_quantity' => 20],
                ],
            ]);

        $response = $this->postJson('/api/v1/sync/bulk/immediate', [
            'shopify_ids' => ['123', '456'],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['success_count', 'failed_count', 'total']);
    }
}
