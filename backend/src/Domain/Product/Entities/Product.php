<?php

declare(strict_types=1);

namespace Src\Domain\Product\Entities;

use DateTimeImmutable;
use InvalidArgumentException;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;
use Src\Domain\Product\ValueObjects\ProductStatus;
use Src\Domain\Product\ValueObjects\Sku;

final readonly class Product
{
    /**
     * @param  array<ProductVariant>  $variants
     */
    private function __construct(
        public Sku $sku,
        public string $title,
        public Price $price,
        public ProductStatus $status,
        public int $inventoryQuantity,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?int $id = null,
        public ?string $description = null,
        public ?string $shopifyId = null,
        public array $variants = [],
    ) {
        if ($inventoryQuantity < 0) {
            throw new InvalidArgumentException('Inventory quantity cannot be negative');
        }
    }

    public static function create(
        Sku $sku,
        string $title,
        Price $price,
        ?string $description = null,
        ?ProductStatus $status = null,
        ?string $shopifyId = null,
        int $inventoryQuantity = 0,
        ?int $id = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            sku: $sku,
            title: $title,
            price: $price,
            status: $status ?? ProductStatus::DRAFT,
            inventoryQuantity: $inventoryQuantity,
            createdAt: $now,
            updatedAt: $now,
            id: $id,
            description: $description,
            shopifyId: $shopifyId,
            variants: [],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromShopifyData(array $data): self
    {
        $variants = $data['variants'] ?? [];

        if (empty($variants)) {
            throw new InvalidArgumentException('Product must have at least one variant');
        }

        $firstVariant = $variants[0];
        $sku = $firstVariant['sku'] ?? null;

        if (empty($sku)) {
            throw new InvalidArgumentException('Product variant must have a SKU');
        }

        $price = isset($firstVariant['price'])
            ? Price::fromDecimal((float) $firstVariant['price'], Currency::USD)
            : Price::zero(Currency::USD);

        $status = ProductStatus::DRAFT;
        if (isset($data['status'])) {
            try {
                $status = ProductStatus::fromString($data['status']);
            } catch (\ValueError) {
                $status = ProductStatus::DRAFT;
            }
        }

        $productVariants = [];
        foreach ($variants as $variantData) {
            if (! empty($variantData['sku'])) {
                $productVariants[] = ProductVariant::fromShopifyData($variantData);
            }
        }

        $now = new DateTimeImmutable;

        return new self(
            sku: new Sku($sku),
            title: $data['title'] ?? 'Untitled Product',
            price: $price,
            status: $status,
            inventoryQuantity: (int) ($firstVariant['inventory_quantity'] ?? 0),
            createdAt: $now,
            updatedAt: $now,
            id: null,
            description: $data['body_html'] ?? null,
            shopifyId: isset($data['id']) ? (string) $data['id'] : null,
            variants: $productVariants,
        );
    }

    /**
     * Creates a new instance with specified property changes (Wither pattern)
     *
     * @param  array<string, mixed>  $changes
     */
    private function with(array $changes): self
    {
        $inventoryQuantity = $changes['inventoryQuantity'] ?? $this->inventoryQuantity;
        if ($inventoryQuantity < 0) {
            throw new InvalidArgumentException('Inventory quantity cannot be negative');
        }

        return new self(
            sku: $this->sku,
            title: $changes['title'] ?? $this->title,
            price: $changes['price'] ?? $this->price,
            status: $changes['status'] ?? $this->status,
            inventoryQuantity: $inventoryQuantity,
            createdAt: $this->createdAt,
            updatedAt: $changes['updatedAt'] ?? new DateTimeImmutable,
            id: $this->id,
            description: $changes['description'] ?? $this->description,
            shopifyId: $this->shopifyId,
            variants: $changes['variants'] ?? $this->variants,
        );
    }

    public function withTitle(string $title): self
    {
        return $this->with(['title' => $title]);
    }

    public function withPrice(Price $price): self
    {
        return $this->with(['price' => $price]);
    }

    public function withStatus(ProductStatus $status): self
    {
        return $this->with(['status' => $status]);
    }

    public function withInventoryQuantity(int $quantity): self
    {
        return $this->with(['inventoryQuantity' => $quantity]);
    }

    public function withDescription(?string $description): self
    {
        return $this->with(['description' => $description]);
    }

    public function addVariant(ProductVariant $variant): self
    {
        $variants = $this->variants;
        $variants[] = $variant;

        return $this->with(['variants' => $variants]);
    }

    public function isInStock(): bool
    {
        return $this->inventoryQuantity > 0;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
