<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $sku
 * @property string $title
 * @property string|null $description
 * @property int $price
 * @property string $currency
 * @property string $status
 * @property int $inventory_quantity
 * @property string|null $shopify_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
final class ProductModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'title',
        'description',
        'price',
        'currency',
        'status',
        'inventory_quantity',
        'shopify_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'integer',
        'inventory_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return HasMany<ProductVariantModel>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInStock(): bool
    {
        return $this->inventory_quantity > 0;
    }
}
