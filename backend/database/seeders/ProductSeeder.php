<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Infrastructure\Persistence\Eloquent\ProductModel;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'DEMO-001',
                'shopify_id' => '632910392',
                'title' => 'Premium Wireless Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 19999,
                'currency' => 'USD',
                'status' => 'active',
                'inventory_quantity' => 150,
            ],
            [
                'sku' => 'DEMO-002',
                'shopify_id' => '921728736',
                'title' => 'Smart Watch Pro',
                'description' => 'Advanced smartwatch with health monitoring features',
                'price' => 29999,
                'currency' => 'USD',
                'status' => 'active',
                'inventory_quantity' => 75,
            ],
            [
                'sku' => 'DEMO-003',
                'shopify_id' => '789456123',
                'title' => 'Portable Bluetooth Speaker',
                'description' => 'Compact speaker with 360-degree sound',
                'price' => 7999,
                'currency' => 'USD',
                'status' => 'active',
                'inventory_quantity' => 200,
            ],
            [
                'sku' => 'DEMO-004',
                'shopify_id' => '456789012',
                'title' => 'USB-C Hub Adapter',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader',
                'price' => 4999,
                'currency' => 'USD',
                'status' => 'active',
                'inventory_quantity' => 300,
            ],
            [
                'sku' => 'DEMO-005',
                'shopify_id' => '123456789',
                'title' => 'Mechanical Keyboard RGB',
                'description' => 'Gaming mechanical keyboard with RGB backlighting',
                'price' => 12999,
                'currency' => 'USD',
                'status' => 'draft',
                'inventory_quantity' => 50,
            ],
        ];

        foreach ($products as $product) {
            ProductModel::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }

        $this->command->info('Seeded ' . count($products) . ' products successfully!');
    }
}
