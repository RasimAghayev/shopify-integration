<?php

declare(strict_types=1);

namespace Src\Domain\Product\Exceptions;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public static function withSku(string $sku): self
    {
        return new self("Product with SKU '{$sku}' not found");
    }

    public static function withShopifyId(string $shopifyId): self
    {
        return new self("Product with Shopify ID '{$shopifyId}' not found");
    }

    public static function withId(int $id): self
    {
        return new self("Product with ID '{$id}' not found");
    }
}
