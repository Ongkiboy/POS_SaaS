<?php

namespace App\Modules\Inventory\Recipe\Models;

use App\Modules\Catalog\Modifier\Models\ModifierOption;
use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bahan tambahan per modifier option. Dipakai memotong stok mulai rilis 1.1 (F-18).
 */
class ModifierOptionRecipeItem extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'modifier_option_id', 'ingredient_id', 'quantity'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['quantity' => 'decimal:4'];
    }

    /**
     * @return BelongsTo<ModifierOption, $this>
     */
    public function modifierOption(): BelongsTo
    {
        return $this->belongsTo(ModifierOption::class);
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
