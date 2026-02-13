<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\SyncProductFromShopify;

use Src\Application\Contracts\CacheInterface;
use Src\Application\Contracts\EventDispatcherInterface;
use Src\Application\Contracts\LoggerInterface;
use Src\Application\Contracts\ShopifyClientInterface;
use Src\Application\Services\CacheKeyGenerator;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Events\ProductSynced;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Throwable;

final readonly class SyncProductFromShopifyUseCase
{
    public function __construct(
        private ShopifyClientInterface $shopifyClient,
        private ProductRepositoryInterface $productRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
        private CacheInterface $cache,
        private CacheKeyGenerator $cacheKeyGenerator,
    ) {}

    public function execute(SyncProductDTO $dto): Product
    {
        $this->logger->info('Starting product sync', [
            'shopify_id' => $dto->shopifyId,
        ]);

        try {
            // Check if product exists and skip if not forcing update
            $existingProduct = $this->productRepository->findByShopifyId($dto->shopifyId);

            if ($existingProduct !== null && ! $dto->forceUpdate) {
                $this->logger->info('Product already exists, skipping sync', [
                    'shopify_id' => $dto->shopifyId,
                    'sku' => $existingProduct->sku->value,
                ]);

                return $existingProduct;
            }

            // Fetch product from Shopify
            $shopifyData = $this->shopifyClient->getProduct($dto->shopifyId);

            // Create or update product entity
            $product = Product::fromShopifyData($shopifyData);

            // If updating existing product, preserve the ID
            if ($existingProduct !== null) {
                $product = $this->mergeWithExisting($product, $existingProduct);
            }

            // Save to repository
            $savedProduct = $this->productRepository->save($product);

            // Invalidate products list cache
            $this->invalidateProductsCache();

            // Dispatch event
            $this->eventDispatcher->dispatch(new ProductSynced($savedProduct));

            $this->logger->info('Product synced successfully', [
                'shopify_id' => $dto->shopifyId,
                'sku' => $savedProduct->sku->value,
            ]);

            return $savedProduct;

        } catch (Throwable $e) {
            $this->logger->error('Product sync failed', [
                'shopify_id' => $dto->shopifyId,
                'error' => $e->getMessage(),
            ]);

            throw ProductSyncFailedException::fromShopifyError($dto->shopifyId, $e);
        }
    }

    private function mergeWithExisting(Product $newProduct, Product $existingProduct): Product
    {
        // Create a new product with updated data but preserved ID
        return Product::create(
            sku: $newProduct->sku,
            title: $newProduct->title,
            price: $newProduct->price,
            description: $newProduct->description,
            status: $newProduct->status,
            shopifyId: $newProduct->shopifyId,
            inventoryQuantity: $newProduct->inventoryQuantity,
            id: $existingProduct->id,
        );
    }

    private function invalidateProductsCache(): void
    {
        // Use tag-based invalidation for efficiency (single operation instead of 80 iterations)
        $this->cache->flushTags([$this->cacheKeyGenerator->getProductsTag()]);
    }
}
