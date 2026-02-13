<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\SyncProductFromShopify;

final readonly class SyncProductDTO
{
    public function __construct(
        public string $shopifyId,
        public bool $forceUpdate = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            shopifyId: (string) ($data['shopify_id'] ?? ''),
            forceUpdate: (bool) ($data['force_update'] ?? true),
        );
    }
}
