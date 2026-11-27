<?php

namespace App\Modules\Inventory\Recipe\Models;

use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recipe berversi (BR-013): satu produk hanya punya satu versi aktif.
 */
class Recipe extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'product_id', 'version', 'is_active', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<RecipeItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }
}
