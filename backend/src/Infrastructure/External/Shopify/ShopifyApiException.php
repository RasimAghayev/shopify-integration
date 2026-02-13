<?php

declare(strict_types=1);

namespace Src\Infrastructure\External\Shopify;

use RuntimeException;
use Throwable;

final class ShopifyApiException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function productNotFound(string $shopifyId): self
    {
        return new self("Product with Shopify ID '{$shopifyId}' not found", 404);
    }

    public static function rateLimited(): self
    {
        return new self('Shopify API rate limit exceeded', 429);
    }

    public static function unauthorized(): self
    {
        return new self('Shopify API authentication failed', 401);
    }
}
