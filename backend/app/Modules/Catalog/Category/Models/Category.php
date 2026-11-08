<?php

namespace App\Modules\Catalog\Category\Models;

use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['business_id', 'name', 'description', 'status', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
