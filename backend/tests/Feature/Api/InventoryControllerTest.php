<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;
use Tests\TestCase;

final class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_inventory(): void
    {
        ProductModel::factory()->create([
            'sku' => 'INV-001',
            'inventory_quantity' => 100,
        ]);

        $response = $this->putJson('/api/v1/inventory', [
            'sku' => 'INV-001',
            'quantity' => 50,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.inventory', 50)
            ->assertJsonPath('message', 'Inventory updated successfully');

        $this->assertDatabaseHas('products', [
            'sku' => 'INV-001',
            'inventory_quantity' => 50,
        ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_sku(): void
    {
        $response = $this->putJson('/api/v1/inventory', [
            'sku' => 'NONEXISTENT',
            'quantity' => 50,
        ]);

        $response->assertNotFound()
            ->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_validates_request_body(): void
    {
        $response = $this->putJson('/api/v1/inventory', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sku', 'quantity']);
    }

    /** @test */
    public function it_validates_quantity_is_not_negative(): void
    {
        ProductModel::factory()->create(['sku' => 'INV-002']);

        $response = $this->putJson('/api/v1/inventory', [
            'sku' => 'INV-002',
            'quantity' => -10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_quantity_is_integer(): void
    {
        $response = $this->putJson('/api/v1/inventory', [
            'sku' => 'INV-003',
            'quantity' => 'not-a-number',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_allows_zero_quantity(): void
    {
        ProductModel::factory()->create([
            'sku' => 'INV-004',
            'inventory_quantity' => 100,
        ]);

        $response = $this->putJson('/api/v1/inventory', [
            'sku' => 'INV-004',
            'quantity' => 0,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.inventory', 0);
    }
}
