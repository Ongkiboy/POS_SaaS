<?php

namespace App\Modules\Inventory\Stock\Models;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use HasOutletScope;

    protected $fillable = [
        'outlet_id',
        'ingredient_id',
        'quantity',
        'min_stock',
        'last_movement_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'min_stock' => 'decimal:4',
            'last_movement_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Outlet, $this>
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * @return BelongsTo<StockMovement, $this>
     */
    public function lastMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'last_movement_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNegative(Builder $query): Builder
    {
        return $query->where('quantity', '<', 0);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('quantity <= COALESCE(stock_balances.min_stock, (SELECT min_stock FROM ingredients WHERE ingredients.id = stock_balances.ingredient_id))');
    }
}
