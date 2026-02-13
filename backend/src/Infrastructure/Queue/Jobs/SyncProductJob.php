<?php

declare(strict_types=1);

namespace Src\Infrastructure\Queue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductFromShopifyUseCase;
use Throwable;

final class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(
        public readonly string $shopifyId,
        public readonly bool $forceUpdate = true,
    ) {}

    public function handle(SyncProductFromShopifyUseCase $useCase): void
    {
        $dto = new SyncProductDTO(
            shopifyId: $this->shopifyId,
            forceUpdate: $this->forceUpdate,
        );

        $useCase->execute($dto);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Product sync job failed', [
            'shopify_id' => $this->shopifyId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function uniqueId(): string
    {
        return "sync-product-{$this->shopifyId}";
    }
}
