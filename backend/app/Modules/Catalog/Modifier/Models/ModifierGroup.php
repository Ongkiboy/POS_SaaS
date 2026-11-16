<?php

namespace App\Modules\Catalog\Modifier\Models;

use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use BelongsToBusiness;
    use SoftDeletes;

    protected $fillable = ['business_id', 'name', 'selection_type', 'is_required', 'min_select', 'max_select', 'sort_order', 'status'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<ModifierOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups')
            ->withPivot('business_id', 'sort_order')
            ->withTimestamps();
    }
}
