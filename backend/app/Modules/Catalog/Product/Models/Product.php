<?php

namespace App\Modules\Catalog\Product\Models;

use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Produk jual. Sengaja tanpa cost_price: HPP dihitung dari recipe (FR-REC-003).
 */
class Product extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['business_id', 'category_id', 'sku', 'name', 'description', 'selling_price', 'status', 'image_path'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['selling_price' => 'integer'];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Saat attach WAJIB mengisi business_id pivot:
     * $product->modifierGroups()->attach($groupId, ['business_id' => $product->business_id]).
     *
     * @return BelongsToMany<ModifierGroup, $this>
     */
    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('business_id', 'sort_order')
            ->withTimestamps();
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
