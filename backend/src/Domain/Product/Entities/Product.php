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

    public function withTitle(string $title): self
    {
        return new self(
            sku: $this->sku,
            title: $title,
            price: $this->price,
            status: $this->status,
            inventoryQuantity: $this->inventoryQuantity,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable,
            id: $this->id,
            description: $this->description,
            shopifyId: $this->shopifyId,
            variants: $this->variants,
        );
    }

    public function withPrice(Price $price): self
    {
        return new self(
            sku: $this->sku,
            title: $this->title,
            price: $price,
            status: $this->status,
            inventoryQuantity: $this->inventoryQuantity,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable,
            id: $this->id,
            description: $this->description,
            shopifyId: $this->shopifyId,
            variants: $this->variants,
        );
    }

    public function withStatus(ProductStatus $status): self
    {
        return new self(
            sku: $this->sku,
            title: $this->title,
            price: $this->price,
            status: $status,
            inventoryQuantity: $this->inventoryQuantity,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable,
            id: $this->id,
            description: $this->description,
            shopifyId: $this->shopifyId,
            variants: $this->variants,
        );
    }

    public function withInventoryQuantity(int $quantity): self
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Inventory quantity cannot be negative');
        }

        return new self(
            sku: $this->sku,
            title: $this->title,
            price: $this->price,
            status: $this->status,
            inventoryQuantity: $quantity,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable,
            id: $this->id,
            description: $this->description,
            shopifyId: $this->shopifyId,
            variants: $this->variants,
        );
    }

    public function addVariant(ProductVariant $variant): self
    {
        $variants = $this->variants;
        $variants[] = $variant;

        return new self(
            sku: $this->sku,
            title: $this->title,
            price: $this->price,
            status: $this->status,
            inventoryQuantity: $this->inventoryQuantity,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable,
            id: $this->id,
            description: $this->description,
            shopifyId: $this->shopifyId,
            variants: $variants,
        );
    }

    public function isInStock(): bool
    {
        return $this->inventoryQuantity > 0;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku->value,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price->amount,
            'currency' => $this->price->currency->value,
            'status' => $this->status->value,
            'inventory_quantity' => $this->inventoryQuantity,
            'shopify_id' => $this->shopifyId,
            'variants' => array_map(static fn (ProductVariant $v) => $v->toArray(), $this->variants),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
