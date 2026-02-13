<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\SyncProductFromShopify;

use RuntimeException;
use Throwable;

final class ProductSyncFailedException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $shopifyId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function fromShopifyError(string $shopifyId, Throwable $exception): self
    {
        return new self(
            message: "Failed to sync product from Shopify: {$exception->getMessage()}",
            shopifyId: $shopifyId,
            previous: $exception,
        );
    }
}
