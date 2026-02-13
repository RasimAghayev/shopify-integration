<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Src\Application\UseCases\Product\GetProductDetails\GetProductDetailsUseCase;
use Src\Application\UseCases\Product\GetProductDetails\GetProductDTO;
use Src\Domain\Product\Exceptions\ProductNotFoundException;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Src\Domain\Product\ValueObjects\Sku;
use Src\Presentation\Http\Resources\ProductCollection;
use Src\Presentation\Http\Resources\ProductResource;

final class ProductController extends Controller
{
    private const int CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetProductDetailsUseCase $getProductUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 10);

        $cacheKey = "products_json_{$page}_{$perPage}";

        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse !== null) {
            return response()->json($cachedResponse);
        }

        $result = $this->productRepository->findAll($page, $perPage);
        $collection = new ProductCollection($result);
        $responseData = $collection->response()->getData(true);

        Cache::put($cacheKey, $responseData, self::CACHE_TTL);

        return response()->json($responseData);
    }

    public function show(string $sku): ProductResource|JsonResponse
    {
        try {
            $dto = GetProductDTO::fromSku($sku);
            $product = $this->getProductUseCase->execute($dto);

            return new ProductResource($product);

        } catch (ProductNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function destroy(string $sku): JsonResponse
    {
        try {
            $this->productRepository->delete(new Sku($sku));

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);

        } catch (ProductNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
