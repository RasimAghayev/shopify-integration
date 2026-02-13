<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\UpdateInventory;

use Src\Application\Contracts\EventDispatcherInterface;
use Src\Application\Contracts\LoggerInterface;
use Src\Domain\Product\Events\InventoryUpdated;
use Src\Domain\Product\Exceptions\ProductNotFoundException;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Src\Domain\Product\ValueObjects\Sku;

final readonly class UpdateInventoryUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {}

    public function execute(UpdateInventoryDTO $dto): void
    {
        $sku = new Sku($dto->sku);

        $this->logger->info('Updating inventory', [
            'sku' => $dto->sku,
            'quantity' => $dto->quantity,
            'reason' => $dto->reason,
        ]);

        $product = $this->productRepository->findBySku($sku);

        if ($product === null) {
            throw ProductNotFoundException::withSku($dto->sku);
        }

        $previousQuantity = $product->inventoryQuantity;

        $updatedProduct = $product->withInventoryQuantity($dto->quantity);

        $this->productRepository->save($updatedProduct);

        $this->eventDispatcher->dispatch(new InventoryUpdated(
            sku: $sku,
            previousQuantity: $previousQuantity,
            newQuantity: $dto->quantity,
            reason: $dto->reason,
        ));

        $this->logger->info('Inventory updated successfully', [
            'sku' => $dto->sku,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $dto->quantity,
        ]);
    }
}
