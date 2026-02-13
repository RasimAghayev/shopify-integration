<?php

declare(strict_types=1);

namespace Src\Infrastructure\External\Shopify;

use Src\Application\Contracts\LoggerInterface;
use Src\Application\Contracts\ShopifyClientInterface;

/**
 * Mock Shopify Client for development and testing
 * Returns consistent, deterministic data without real API calls
 */
final class MockShopifyClient implements ShopifyClientInterface
{
    private array $products;

    private array $inventory;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $this->initializeMockData();
    }

    /**
     * @return array<string, mixed>
     */
    public function getProduct(string $shopifyId): array
    {
        $this->logger->debug('MockShopifyClient::getProduct', ['shopify_id' => $shopifyId]);

        $id = (int) $shopifyId;

        if (! isset($this->products[$id])) {
            throw new ShopifyApiException(
                "Product not found with ID: {$shopifyId}",
                404,
            );
        }

        return $this->products[$id];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(int $page = 1, int $limit = 50): array
    {
        $this->logger->debug('MockShopifyClient::getProducts', [
            'page' => $page,
            'limit' => $limit,
        ]);

        $allProducts = array_values($this->products);
        $offset = ($page - 1) * $limit;

        return array_slice($allProducts, $offset, $limit);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateProduct(string $shopifyId, array $data): array
    {
        $this->logger->debug('MockShopifyClient::updateProduct', [
            'shopify_id' => $shopifyId,
            'data' => $data,
        ]);

        $id = (int) $shopifyId;

        if (! isset($this->products[$id])) {
            throw new ShopifyApiException(
                "Product not found with ID: {$shopifyId}",
                404,
            );
        }

        // Simulate update - merge data
        $product = $this->products[$id];

        if (isset($data['title'])) {
            $product['title'] = $data['title'];
        }
        if (isset($data['body_html'])) {
            $product['body_html'] = $data['body_html'];
        }
        if (isset($data['status'])) {
            $product['status'] = $data['status'];
        }
        if (isset($data['tags'])) {
            $product['tags'] = $data['tags'];
        }

        $product['updated_at'] = now()->toIso8601String();

        // In real implementation would persist, but mock just returns updated
        $this->products[$id] = $product;

        return $product;
    }

    public function getProductsCount(): int
    {
        $this->logger->debug('MockShopifyClient::getProductsCount');

        return count($this->products);
    }

    /**
     * @return array<string, mixed>
     */
    public function updateInventory(string $inventoryItemId, int $quantity): array
    {
        $this->logger->debug('MockShopifyClient::updateInventory', [
            'inventory_item_id' => $inventoryItemId,
            'quantity' => $quantity,
        ]);

        $id = (int) $inventoryItemId;

        if (! isset($this->inventory[$id])) {
            $this->logger->warning('Inventory item not found, creating mock entry', [
                'inventory_item_id' => $inventoryItemId,
            ]);

            $this->inventory[$id] = [
                'inventory_item_id' => $id,
                'location_id' => 1001,
                'available' => $quantity,
                'updated_at' => now()->toIso8601String(),
            ];
        } else {
            $this->inventory[$id]['available'] = $quantity;
            $this->inventory[$id]['updated_at'] = now()->toIso8601String();
        }

        // Also update the variant in products
        foreach ($this->products as &$product) {
            foreach ($product['variants'] as &$variant) {
                if ($variant['inventory_item_id'] === $id) {
                    $variant['inventory_quantity'] = $quantity;
                    break 2;
                }
            }
        }

        return $this->inventory[$id];
    }

    /**
     * Initialize mock product data - always returns same data
     */
    private function initializeMockData(): void
    {
        $this->products = [
            632910392 => [
                'id' => 632910392,
                'title' => 'IPod Nano - 8GB',
                'body_html' => '<p>It\'s the small iPod with one big idea: Video. Now the world\'s most popular music player lets you enjoy TV shows, movies, and more on a beautiful 2-inch display.</p>',
                'vendor' => 'Apple',
                'product_type' => 'Electronics',
                'handle' => 'ipod-nano-8gb',
                'status' => 'active',
                'published_scope' => 'global',
                'tags' => 'Emotive, Flash Memory, MP3, Music',
                'template_suffix' => null,
                'created_at' => '2024-01-15T09:00:00+00:00',
                'updated_at' => '2024-02-10T14:30:00+00:00',
                'published_at' => '2024-01-15T09:00:00+00:00',
                'admin_graphql_api_id' => 'gid://shopify/Product/632910392',
                'options' => [
                    [
                        'id' => 594680422,
                        'product_id' => 632910392,
                        'name' => 'Color',
                        'position' => 1,
                        'values' => ['Pink', 'Red', 'Green', 'Black'],
                    ],
                ],
                'images' => [
                    [
                        'id' => 850703190,
                        'product_id' => 632910392,
                        'position' => 1,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/ipod-nano.png',
                        'width' => 600,
                        'height' => 600,
                        'alt' => 'IPod Nano - 8GB',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 808950810,
                        'product_id' => 632910392,
                        'title' => 'Pink',
                        'price' => '199.00',
                        'sku' => 'IPOD2008PINK',
                        'position' => 1,
                        'inventory_policy' => 'continue',
                        'compare_at_price' => '249.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'Pink',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => '1234_pink',
                        'grams' => 200,
                        'weight' => 0.2,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 808950810,
                        'inventory_quantity' => 10,
                        'old_inventory_quantity' => 10,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 49148385,
                        'product_id' => 632910392,
                        'title' => 'Red',
                        'price' => '199.00',
                        'sku' => 'IPOD2008RED',
                        'position' => 2,
                        'inventory_policy' => 'continue',
                        'compare_at_price' => '249.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'Red',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => '1234_red',
                        'grams' => 200,
                        'weight' => 0.2,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 49148385,
                        'inventory_quantity' => 20,
                        'old_inventory_quantity' => 20,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 39072856,
                        'product_id' => 632910392,
                        'title' => 'Green',
                        'price' => '199.00',
                        'sku' => 'IPOD2008GREEN',
                        'position' => 3,
                        'inventory_policy' => 'continue',
                        'compare_at_price' => '249.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'Green',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => '1234_green',
                        'grams' => 200,
                        'weight' => 0.2,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 39072856,
                        'inventory_quantity' => 30,
                        'old_inventory_quantity' => 30,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 457924702,
                        'product_id' => 632910392,
                        'title' => 'Black',
                        'price' => '199.00',
                        'sku' => 'IPOD2008BLACK',
                        'position' => 4,
                        'inventory_policy' => 'continue',
                        'compare_at_price' => '249.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'Black',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => '1234_black',
                        'grams' => 200,
                        'weight' => 0.2,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 457924702,
                        'inventory_quantity' => 40,
                        'old_inventory_quantity' => 40,
                        'requires_shipping' => true,
                    ],
                ],
            ],
            921728736 => [
                'id' => 921728736,
                'title' => 'Burton Custom Freestyle 151',
                'body_html' => '<strong>Good snowboard!</strong><p>This is a great snowboard for beginners and intermediate riders.</p>',
                'vendor' => 'Burton',
                'product_type' => 'Snowboard',
                'handle' => 'burton-custom-freestyle-151',
                'status' => 'active',
                'published_scope' => 'global',
                'tags' => 'Barnes & Noble, Big Air, Snow, Winter Sports',
                'template_suffix' => null,
                'created_at' => '2024-01-20T10:30:00+00:00',
                'updated_at' => '2024-02-08T11:45:00+00:00',
                'published_at' => '2024-01-20T10:30:00+00:00',
                'admin_graphql_api_id' => 'gid://shopify/Product/921728736',
                'options' => [
                    [
                        'id' => 891236789,
                        'product_id' => 921728736,
                        'name' => 'Size',
                        'position' => 1,
                        'values' => ['151', '155', '158'],
                    ],
                ],
                'images' => [
                    [
                        'id' => 562641783,
                        'product_id' => 921728736,
                        'position' => 1,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/burton-snowboard.png',
                        'width' => 800,
                        'height' => 800,
                        'alt' => 'Burton Custom Freestyle 151',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 447654529,
                        'product_id' => 921728736,
                        'title' => '151',
                        'price' => '499.00',
                        'sku' => 'BURTON-151',
                        'position' => 1,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '599.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '151',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'BURTON151',
                        'grams' => 3500,
                        'weight' => 3.5,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 447654529,
                        'inventory_quantity' => 5,
                        'old_inventory_quantity' => 5,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 447654530,
                        'product_id' => 921728736,
                        'title' => '155',
                        'price' => '519.00',
                        'sku' => 'BURTON-155',
                        'position' => 2,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '619.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '155',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'BURTON155',
                        'grams' => 3700,
                        'weight' => 3.7,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 447654530,
                        'inventory_quantity' => 8,
                        'old_inventory_quantity' => 8,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 447654531,
                        'product_id' => 921728736,
                        'title' => '158',
                        'price' => '539.00',
                        'sku' => 'BURTON-158',
                        'position' => 3,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '639.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '158',
                        'option2' => null,
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'BURTON158',
                        'grams' => 3900,
                        'weight' => 3.9,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 447654531,
                        'inventory_quantity' => 12,
                        'old_inventory_quantity' => 12,
                        'requires_shipping' => true,
                    ],
                ],
            ],
            789456123 => [
                'id' => 789456123,
                'title' => 'Nike Air Max 90',
                'body_html' => '<p>The Nike Air Max 90 stays true to its OG running roots with the iconic Waffle sole, stitched overlays and classic TPU details.</p>',
                'vendor' => 'Nike',
                'product_type' => 'Shoes',
                'handle' => 'nike-air-max-90',
                'status' => 'active',
                'published_scope' => 'global',
                'tags' => 'Athletic, Footwear, Nike, Running, Shoes',
                'template_suffix' => null,
                'created_at' => '2024-02-01T08:00:00+00:00',
                'updated_at' => '2024-02-11T09:15:00+00:00',
                'published_at' => '2024-02-01T08:00:00+00:00',
                'admin_graphql_api_id' => 'gid://shopify/Product/789456123',
                'options' => [
                    [
                        'id' => 112233445,
                        'product_id' => 789456123,
                        'name' => 'Size',
                        'position' => 1,
                        'values' => ['40', '41', '42', '43', '44'],
                    ],
                    [
                        'id' => 112233446,
                        'product_id' => 789456123,
                        'name' => 'Color',
                        'position' => 2,
                        'values' => ['White', 'Black'],
                    ],
                ],
                'images' => [
                    [
                        'id' => 998877665,
                        'product_id' => 789456123,
                        'position' => 1,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/nike-air-max-90-white.png',
                        'width' => 1000,
                        'height' => 1000,
                        'alt' => 'Nike Air Max 90 White',
                    ],
                    [
                        'id' => 998877666,
                        'product_id' => 789456123,
                        'position' => 2,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/nike-air-max-90-black.png',
                        'width' => 1000,
                        'height' => 1000,
                        'alt' => 'Nike Air Max 90 Black',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 556677881,
                        'product_id' => 789456123,
                        'title' => '40 / White',
                        'price' => '159.00',
                        'sku' => 'NIKE-AM90-40-WHT',
                        'position' => 1,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '189.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '40',
                        'option2' => 'White',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'NIKEAM9040W',
                        'grams' => 400,
                        'weight' => 0.4,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 556677881,
                        'inventory_quantity' => 15,
                        'old_inventory_quantity' => 15,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 556677882,
                        'product_id' => 789456123,
                        'title' => '41 / White',
                        'price' => '159.00',
                        'sku' => 'NIKE-AM90-41-WHT',
                        'position' => 2,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '189.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '41',
                        'option2' => 'White',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'NIKEAM9041W',
                        'grams' => 420,
                        'weight' => 0.42,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 556677882,
                        'inventory_quantity' => 20,
                        'old_inventory_quantity' => 20,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 556677883,
                        'product_id' => 789456123,
                        'title' => '42 / White',
                        'price' => '159.00',
                        'sku' => 'NIKE-AM90-42-WHT',
                        'position' => 3,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '189.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '42',
                        'option2' => 'White',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'NIKEAM9042W',
                        'grams' => 440,
                        'weight' => 0.44,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 556677883,
                        'inventory_quantity' => 25,
                        'old_inventory_quantity' => 25,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 556677884,
                        'product_id' => 789456123,
                        'title' => '42 / Black',
                        'price' => '159.00',
                        'sku' => 'NIKE-AM90-42-BLK',
                        'position' => 4,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '189.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '42',
                        'option2' => 'Black',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'NIKEAM9042B',
                        'grams' => 440,
                        'weight' => 0.44,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 556677884,
                        'inventory_quantity' => 18,
                        'old_inventory_quantity' => 18,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 556677885,
                        'product_id' => 789456123,
                        'title' => '43 / Black',
                        'price' => '159.00',
                        'sku' => 'NIKE-AM90-43-BLK',
                        'position' => 5,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '189.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '43',
                        'option2' => 'Black',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'NIKEAM9043B',
                        'grams' => 460,
                        'weight' => 0.46,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 556677885,
                        'inventory_quantity' => 22,
                        'old_inventory_quantity' => 22,
                        'requires_shipping' => true,
                    ],
                ],
            ],
            456789012 => [
                'id' => 456789012,
                'title' => 'Samsung Galaxy S24 Ultra',
                'body_html' => '<p>Experience the ultimate Galaxy with the new S24 Ultra. Featuring a 200MP camera, S Pen, and AI-powered features.</p>',
                'vendor' => 'Samsung',
                'product_type' => 'Electronics',
                'handle' => 'samsung-galaxy-s24-ultra',
                'status' => 'active',
                'published_scope' => 'global',
                'tags' => 'Android, Mobile, Phone, Samsung, Smartphone',
                'template_suffix' => null,
                'created_at' => '2024-01-25T12:00:00+00:00',
                'updated_at' => '2024-02-09T16:20:00+00:00',
                'published_at' => '2024-01-25T12:00:00+00:00',
                'admin_graphql_api_id' => 'gid://shopify/Product/456789012',
                'options' => [
                    [
                        'id' => 334455667,
                        'product_id' => 456789012,
                        'name' => 'Storage',
                        'position' => 1,
                        'values' => ['256GB', '512GB', '1TB'],
                    ],
                    [
                        'id' => 334455668,
                        'product_id' => 456789012,
                        'name' => 'Color',
                        'position' => 2,
                        'values' => ['Titanium Black', 'Titanium Gray', 'Titanium Violet'],
                    ],
                ],
                'images' => [
                    [
                        'id' => 223344556,
                        'product_id' => 456789012,
                        'position' => 1,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/samsung-s24-ultra.png',
                        'width' => 1200,
                        'height' => 1200,
                        'alt' => 'Samsung Galaxy S24 Ultra',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 998877661,
                        'product_id' => 456789012,
                        'title' => '256GB / Titanium Black',
                        'price' => '1299.00',
                        'sku' => 'SAMSUNG-S24U-256-BLK',
                        'position' => 1,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '1399.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '256GB',
                        'option2' => 'Titanium Black',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'SAMS24U256B',
                        'grams' => 233,
                        'weight' => 0.233,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 998877661,
                        'inventory_quantity' => 50,
                        'old_inventory_quantity' => 50,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 998877662,
                        'product_id' => 456789012,
                        'title' => '512GB / Titanium Gray',
                        'price' => '1419.00',
                        'sku' => 'SAMSUNG-S24U-512-GRY',
                        'position' => 2,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '1519.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '512GB',
                        'option2' => 'Titanium Gray',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'SAMS24U512G',
                        'grams' => 233,
                        'weight' => 0.233,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 998877662,
                        'inventory_quantity' => 35,
                        'old_inventory_quantity' => 35,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 998877663,
                        'product_id' => 456789012,
                        'title' => '1TB / Titanium Violet',
                        'price' => '1659.00',
                        'sku' => 'SAMSUNG-S24U-1TB-VIO',
                        'position' => 3,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '1759.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => '1TB',
                        'option2' => 'Titanium Violet',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'SAMS24U1TBV',
                        'grams' => 233,
                        'weight' => 0.233,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 998877663,
                        'inventory_quantity' => 20,
                        'old_inventory_quantity' => 20,
                        'requires_shipping' => true,
                    ],
                ],
            ],
            123456789 => [
                'id' => 123456789,
                'title' => 'Organic Cotton T-Shirt',
                'body_html' => '<p>100% organic cotton t-shirt. Soft, comfortable, and eco-friendly. Perfect for everyday wear.</p>',
                'vendor' => 'EcoWear',
                'product_type' => 'Apparel',
                'handle' => 'organic-cotton-t-shirt',
                'status' => 'active',
                'published_scope' => 'global',
                'tags' => 'Clothing, Cotton, Eco-friendly, Organic, T-Shirt',
                'template_suffix' => null,
                'created_at' => '2024-02-05T14:00:00+00:00',
                'updated_at' => '2024-02-11T10:00:00+00:00',
                'published_at' => '2024-02-05T14:00:00+00:00',
                'admin_graphql_api_id' => 'gid://shopify/Product/123456789',
                'options' => [
                    [
                        'id' => 778899001,
                        'product_id' => 123456789,
                        'name' => 'Size',
                        'position' => 1,
                        'values' => ['S', 'M', 'L', 'XL'],
                    ],
                    [
                        'id' => 778899002,
                        'product_id' => 123456789,
                        'name' => 'Color',
                        'position' => 2,
                        'values' => ['White', 'Navy', 'Forest Green'],
                    ],
                ],
                'images' => [
                    [
                        'id' => 445566778,
                        'product_id' => 123456789,
                        'position' => 1,
                        'src' => 'https://cdn.shopify.com/s/files/1/0000/0001/products/organic-tshirt.png',
                        'width' => 800,
                        'height' => 800,
                        'alt' => 'Organic Cotton T-Shirt',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 112233441,
                        'product_id' => 123456789,
                        'title' => 'S / White',
                        'price' => '29.00',
                        'sku' => 'ECO-TSHIRT-S-WHT',
                        'position' => 1,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '39.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'S',
                        'option2' => 'White',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'ECOTSSW',
                        'grams' => 180,
                        'weight' => 0.18,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 112233441,
                        'inventory_quantity' => 100,
                        'old_inventory_quantity' => 100,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 112233442,
                        'product_id' => 123456789,
                        'title' => 'M / White',
                        'price' => '29.00',
                        'sku' => 'ECO-TSHIRT-M-WHT',
                        'position' => 2,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '39.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'M',
                        'option2' => 'White',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'ECOTSMW',
                        'grams' => 190,
                        'weight' => 0.19,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 112233442,
                        'inventory_quantity' => 150,
                        'old_inventory_quantity' => 150,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 112233443,
                        'product_id' => 123456789,
                        'title' => 'L / Navy',
                        'price' => '29.00',
                        'sku' => 'ECO-TSHIRT-L-NVY',
                        'position' => 3,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '39.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'L',
                        'option2' => 'Navy',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'ECOTSLN',
                        'grams' => 200,
                        'weight' => 0.20,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 112233443,
                        'inventory_quantity' => 80,
                        'old_inventory_quantity' => 80,
                        'requires_shipping' => true,
                    ],
                    [
                        'id' => 112233444,
                        'product_id' => 123456789,
                        'title' => 'XL / Forest Green',
                        'price' => '29.00',
                        'sku' => 'ECO-TSHIRT-XL-GRN',
                        'position' => 4,
                        'inventory_policy' => 'deny',
                        'compare_at_price' => '39.00',
                        'fulfillment_service' => 'manual',
                        'inventory_management' => 'shopify',
                        'option1' => 'XL',
                        'option2' => 'Forest Green',
                        'option3' => null,
                        'taxable' => true,
                        'barcode' => 'ECOTSXLG',
                        'grams' => 210,
                        'weight' => 0.21,
                        'weight_unit' => 'kg',
                        'inventory_item_id' => 112233444,
                        'inventory_quantity' => 60,
                        'old_inventory_quantity' => 60,
                        'requires_shipping' => true,
                    ],
                ],
            ],
        ];

        // Initialize inventory tracking
        $this->inventory = [];
        foreach ($this->products as $product) {
            foreach ($product['variants'] as $variant) {
                $this->inventory[$variant['inventory_item_id']] = [
                    'inventory_item_id' => $variant['inventory_item_id'],
                    'location_id' => 1001,
                    'available' => $variant['inventory_quantity'],
                    'updated_at' => now()->toIso8601String(),
                ];
            }
        }
    }
}
