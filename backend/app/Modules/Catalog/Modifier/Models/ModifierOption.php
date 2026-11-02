<?php

namespace App\Modules\Catalog\Modifier\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierOption extends Model
{
    use BelongsToBusiness;
    use SoftDeletes;

    protected $fillable = ['business_id', 'modifier_group_id', 'name', 'price_delta', 'sort_order', 'status'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_delta' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ModifierGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }
}
