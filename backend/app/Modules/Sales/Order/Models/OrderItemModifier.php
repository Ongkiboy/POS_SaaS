<?php

namespace App\Modules\Sales\Order\Models;

use App\Modules\Catalog\Modifier\Models\ModifierOption;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'order_item_id', 'modifier_option_id', 'name_snapshot', 'price_delta'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['price_delta' => 'integer'];
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * @return BelongsTo<ModifierOption, $this>
     */
    public function modifierOption(): BelongsTo
    {
        return $this->belongsTo(ModifierOption::class);
    }
}
