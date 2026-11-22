<?php

namespace App\Modules\Sales\Order\Models;

use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Inventory\Recipe\Models\Recipe;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Item order dengan snapshot nama, SKU, harga, HPP, dan versi recipe (BR-014).
 */
class OrderItem extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'order_id', 'product_id', 'recipe_id', 'product_name_snapshot', 'sku_snapshot',
        'unit_price', 'unit_cost_snapshot', 'quantity', 'line_total', 'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'unit_cost_snapshot' => 'integer',
            'quantity' => 'integer',
            'line_total' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Versi recipe saat order dibuat, bukan recipe aktif sekarang.
     *
     * @return BelongsTo<Recipe, $this>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * @return HasMany<OrderItemModifier, $this>
     */
    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }
}
