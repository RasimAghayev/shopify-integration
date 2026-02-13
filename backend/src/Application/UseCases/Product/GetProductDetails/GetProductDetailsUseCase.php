<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\GetProductDetails;

use Src\Application\Contracts\CacheInterface;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Exceptions\ProductNotFoundException;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Src\Domain\Product\ValueObjects\Sku;

final readonly class GetProductDetailsUseCase
{
    private const int CACHE_TTL = 3600;

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CacheInterface $cache,
    ) {}

    public function execute(GetProductDTO $dto): Product
    {
        $cacheKey = $this->getCacheKey($dto);

        return $this->cache->remember($cacheKey, self::CACHE_TTL, function () use ($dto) {
            $product = $this->findProduct($dto);

            if ($product === null) {
                throw $this->createNotFoundException($dto);
            }

            return $product;
        });
    }

    private function findProduct(GetProductDTO $dto): ?Product
    {
        if ($dto->sku !== null) {
            return $this->productRepository->findBySku(new Sku($dto->sku));
        }

        if ($dto->id !== null) {
            return $this->productRepository->findById($dto->id);
        }

        if ($dto->shopifyId !== null) {
            return $this->productRepository->findByShopifyId($dto->shopifyId);
        }

        return null;
    }

    private function getCacheKey(GetProductDTO $dto): string
    {
        if ($dto->sku !== null) {
            return "product.sku.{$dto->sku}";
        }

        if ($dto->id !== null) {
            return "product.id.{$dto->id}";
        }

        return "product.shopify.{$dto->shopifyId}";
    }

    private function createNotFoundException(GetProductDTO $dto): ProductNotFoundException
    {
        if ($dto->sku !== null) {
            return ProductNotFoundException::withSku($dto->sku);
        }

        if ($dto->id !== null) {
            return ProductNotFoundException::withId($dto->id);
        }

        return ProductNotFoundException::withShopifyId($dto->shopifyId ?? '');
    }
}
