<?php

declare(strict_types=1);

namespace Src\Domain\Product\Events;

use DateTimeImmutable;
use Src\Domain\Product\Entities\Product;

final readonly class ProductCreated
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public Product $product,
    ) {
        $this->occurredAt = new DateTimeImmutable;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->product->id,
            'sku' => $this->product->sku->value,
            'title' => $this->product->title,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
