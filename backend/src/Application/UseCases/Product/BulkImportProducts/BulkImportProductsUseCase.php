<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\BulkImportProducts;

use Src\Application\Contracts\LoggerInterface;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductFromShopifyUseCase;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Throwable;

final readonly class BulkImportProductsUseCase
{
    public function __construct(
        private SyncProductFromShopifyUseCase $syncUseCase,
        private ProductRepositoryInterface $productRepository,
        private LoggerInterface $logger,
    ) {}

    public function execute(BulkImportDTO $dto): BulkImportResult
    {
        $this->logger->info('Starting bulk import', [
            'count' => $dto->count(),
            'skip_duplicates' => $dto->skipDuplicates,
        ]);

        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($dto->shopifyIds as $shopifyId) {
            try {
                // Check for duplicates
                if ($dto->skipDuplicates && $this->productRepository->existsByShopifyId($shopifyId)) {
                    $skippedCount++;
                    $this->logger->debug('Skipping duplicate product', [
                        'shopify_id' => $shopifyId,
                    ]);

                    continue;
                }

                // Sync product
                $syncDto = new SyncProductDTO(
                    shopifyId: $shopifyId,
                    forceUpdate: ! $dto->skipDuplicates,
                );

                $this->syncUseCase->execute($syncDto);
                $successCount++;

            } catch (Throwable $e) {
                $failedCount++;
                $errors[] = [
                    'shopifyId' => $shopifyId,
                    'error' => $e->getMessage(),
                ];

                $this->logger->error('Failed to import product', [
                    'shopify_id' => $shopifyId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result = new BulkImportResult(
            successCount: $successCount,
            failedCount: $failedCount,
            skippedCount: $skippedCount,
            errors: $errors,
        );

        $this->logger->info('Bulk import completed', $result->toArray());

        return $result;
    }
}
