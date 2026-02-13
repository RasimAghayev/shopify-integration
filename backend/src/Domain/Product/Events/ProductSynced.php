<?php

declare(strict_types=1);

namespace Src\Domain\Product\Events;

use DateTimeImmutable;
use Src\Domain\Product\Entities\Product;

final readonly class ProductSynced
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public Product $product,
        public string $source = 'shopify',
    ) {
        $this->occurredAt = new DateTimeImmutable;
    }

    public function productId(): ?int
    {
        return $this->product->id;
    }

    public function sku(): string
    {
        return $this->product->sku->value;
    }

    public function shopifyId(): ?string
    {
        return $this->product->shopifyId;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->product->id,
            'sku' => $this->product->sku->value,
            'shopify_id' => $this->product->shopifyId,
            'source' => $this->source,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
