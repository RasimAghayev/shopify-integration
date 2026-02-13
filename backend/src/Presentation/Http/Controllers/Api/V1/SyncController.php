<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\Application\UseCases\Product\BulkImportProducts\BulkImportDTO;
use Src\Application\UseCases\Product\BulkImportProducts\BulkImportProductsUseCase;
use Src\Application\UseCases\Product\SyncProductFromShopify\ProductSyncFailedException;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductFromShopifyUseCase;
use Src\Infrastructure\Queue\Jobs\SyncProductJob;
use Src\Presentation\Http\Requests\BulkSyncRequest;
use Src\Presentation\Http\Requests\SyncProductRequest;
use Src\Presentation\Http\Resources\ProductResource;
use Symfony\Component\HttpFoundation\Response;

final class SyncController extends Controller
{
    public function __construct(
        private readonly SyncProductFromShopifyUseCase $syncUseCase,
        private readonly BulkImportProductsUseCase $bulkImportUseCase,
    ) {}

    public function syncProduct(SyncProductRequest $request): JsonResponse
    {
        try {
            $dto = new SyncProductDTO(
                shopifyId: $request->validated('shopify_id'),
                forceUpdate: $request->validated('force_update', true),
            );

            $product = $this->syncUseCase->execute($dto);

            return response()->json(
                new ProductResource($product),
                Response::HTTP_CREATED,
            );

        } catch (ProductSyncFailedException $e) {
            return response()->json([
                'error' => 'Sync failed',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bulkSync(BulkSyncRequest $request): JsonResponse
    {
        $shopifyIds = $request->validated('shopify_ids');

        // Queue jobs for async processing
        foreach ($shopifyIds as $shopifyId) {
            SyncProductJob::dispatch($shopifyId);
        }

        return response()->json([
            'message' => 'Sync jobs queued',
            'count' => count($shopifyIds),
        ], Response::HTTP_ACCEPTED);
    }

    public function bulkSyncImmediate(BulkSyncRequest $request): JsonResponse
    {
        $dto = new BulkImportDTO(
            shopifyIds: $request->validated('shopify_ids'),
            skipDuplicates: $request->validated('skip_duplicates', true),
        );

        $result = $this->bulkImportUseCase->execute($dto);

        return response()->json($result->toArray());
    }
}
