<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\Application\UseCases\Product\UpdateInventory\UpdateInventoryDTO;
use Src\Application\UseCases\Product\UpdateInventory\UpdateInventoryUseCase;
use Src\Domain\Product\Exceptions\ProductNotFoundException;
use Src\Presentation\Http\Requests\UpdateInventoryRequest;
use Symfony\Component\HttpFoundation\Response;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly UpdateInventoryUseCase $updateInventoryUseCase,
    ) {}

    public function update(UpdateInventoryRequest $request): JsonResponse
    {
        try {
            $dto = new UpdateInventoryDTO(
                sku: $request->validated('sku'),
                quantity: $request->validated('quantity'),
                reason: $request->validated('reason'),
            );

            $this->updateInventoryUseCase->execute($dto);

            return response()->json([
                'message' => 'Inventory updated successfully',
                'sku' => $dto->sku,
                'quantity' => $dto->quantity,
            ]);

        } catch (ProductNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
