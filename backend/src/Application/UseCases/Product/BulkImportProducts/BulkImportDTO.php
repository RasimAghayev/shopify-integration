<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\BulkImportProducts;

final readonly class BulkImportDTO
{
    /**
     * @param  array<string>  $shopifyIds
     */
    public function __construct(
        public array $shopifyIds,
        public bool $skipDuplicates = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            shopifyIds: $data['shopify_ids'] ?? [],
            skipDuplicates: (bool) ($data['skip_duplicates'] ?? true),
        );
    }

    public function count(): int
    {
        return count($this->shopifyIds);
    }
}
