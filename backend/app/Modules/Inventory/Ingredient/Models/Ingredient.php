<?php

namespace App\Modules\Inventory\Ingredient\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Database\Factories\IngredientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<IngredientFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['business_id', 'base_unit_id', 'sku', 'name', 'last_cost', 'min_stock', 'status'];

    /**
     * decimal:4 = dibaca sebagai string 4 desimal, bukan float (ERD §7.2).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_cost' => 'decimal:4',
            'min_stock' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * @return HasMany<IngredientUnitConversion, $this>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(IngredientUnitConversion::class);
    }

    protected static function newFactory(): IngredientFactory
    {
        return IngredientFactory::new();
    }
}
