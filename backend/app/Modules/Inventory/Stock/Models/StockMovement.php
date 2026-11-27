<?php

namespace App\Modules\Inventory\Stock\Models;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Buku besar pergerakan stok append-only (ADR-007, BR-020).
 */
class StockMovement extends Model
{
    use HasOutletScope;

    public const UPDATED_AT = null;

    protected $fillable = [
        'outlet_id',
        'ingredient_id',
        'type',
        'quantity_delta',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_delta' => 'decimal:4',
            'quantity_before' => 'decimal:4',
            'quantity_after' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'reference_id' => 'integer',
            'occurred_at' => 'datetime',
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

    /**
     * Polimorfik tanpa FK fisik (ERD §7.1).
     *
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
