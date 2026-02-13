<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\GetProductDetails;

final readonly class GetProductDTO
{
    public function __construct(
        public ?string $sku = null,
        public ?int $id = null,
        public ?string $shopifyId = null,
    ) {
        if ($sku === null && $id === null && $shopifyId === null) {
            throw new \InvalidArgumentException('At least one identifier (sku, id, or shopifyId) must be provided');
        }
    }

    public static function fromSku(string $sku): self
    {
        return new self(sku: $sku);
    }

    public static function fromId(int $id): self
    {
        return new self(id: $id);
    }

    public static function fromShopifyId(string $shopifyId): self
    {
        return new self(shopifyId: $shopifyId);
    }
}
