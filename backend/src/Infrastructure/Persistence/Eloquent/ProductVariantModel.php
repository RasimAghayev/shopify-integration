<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property int $price
 * @property string $currency
 * @property int $inventory_quantity
 * @property string|null $shopify_variant_id
 * @property float|null $weight
 * @property string|null $weight_unit
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class ProductVariantModel extends Model
{
    use HasFactory;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'currency',
        'inventory_quantity',
        'shopify_variant_id',
        'weight',
        'weight_unit',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
        'price' => 'integer',
        'inventory_quantity' => 'integer',
        'weight' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<ProductModel, ProductVariantModel>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function isInStock(): bool
    {
        return $this->inventory_quantity > 0;
    }
}
