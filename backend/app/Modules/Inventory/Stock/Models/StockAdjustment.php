<?php

namespace App\Modules\Inventory\Stock\Models;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasOutletScope;

    public const UPDATED_AT = null;

    protected $fillable = [
        'outlet_id',
        'ingredient_id',
        'opname_item_id',
        'quantity_before',
        'quantity_delta',
        'quantity_after',
        'reason',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_before' => 'decimal:4',
            'quantity_delta' => 'decimal:4',
            'quantity_after' => 'decimal:4',
            'opname_item_id' => 'integer',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
