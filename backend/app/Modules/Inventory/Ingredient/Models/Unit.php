<?php

namespace App\Modules\Inventory\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satuan global: TIDAK memakai BelongsToBusiness (tidak punya business_id).
 */
class Unit extends Model
{
    protected $fillable = ['code', 'name'];

    /**
     * @return HasMany<Ingredient, $this>
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'base_unit_id');
    }
}
