<?php

declare(strict_types=1);

namespace Src\Domain\Product\Exceptions;

use RuntimeException;

final class DuplicateProductException extends RuntimeException
{
    public static function withSku(string $sku): self
    {
        return new self("Product with SKU '{$sku}' already exists");
    }

    public static function withShopifyId(string $shopifyId): self
    {
        return new self("Product with Shopify ID '{$shopifyId}' already exists");
    }
}
