<?php

namespace App\Modules\Sales\Order\Models;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Platform\User\Models\User;
use App\Modules\Shift\Models\Shift;
use App\Support\Enums\DiscountType;
use App\Support\Enums\KitchenStatus;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order kasir (ERD §3.4). Uang = int rupiah; tarif = string 2 desimal.
 */
class Order extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use HasOutletScope;

    protected $fillable = [
        'business_id', 'outlet_id', 'shift_id', 'cashier_id', 'business_date', 'order_number',
        'order_type', 'table_number', 'status', 'kitchen_status',
        'subtotal', 'discount_type', 'discount_value', 'discount_amount', 'service_charge_amount',
        'tax_amount', 'rounding_amount', 'total', 'paid_amount', 'tax_rate_snapshot', 'service_rate_snapshot',
        'note', 'cancel_reason', 'cancelled_by', 'paid_at', 'completed_at', 'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'order_type' => OrderType::class,
            'status' => OrderStatus::class,
            'kitchen_status' => KitchenStatus::class,
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'service_charge_amount' => 'integer',
            'tax_amount' => 'integer',
            'rounding_amount' => 'integer',
            'total' => 'integer',
            'paid_amount' => 'integer',
            'tax_rate_snapshot' => 'decimal:2',
            'service_rate_snapshot' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Outlet, $this>
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
