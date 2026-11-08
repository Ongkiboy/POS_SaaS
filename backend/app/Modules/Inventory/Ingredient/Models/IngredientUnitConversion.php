<?php

namespace App\Modules\Inventory\Ingredient\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientUnitConversion extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'ingredient_id', 'unit_id', 'factor_to_base'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['factor_to_base' => 'decimal:6'];
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
