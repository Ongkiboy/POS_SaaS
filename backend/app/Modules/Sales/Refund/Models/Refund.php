<?php

namespace App\Modules\Sales\Refund\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Platform\User\Models\User;
use App\Modules\Sales\Order\Models\Order;
use App\Modules\Sales\Payment\Models\Payment;
use App\Modules\Shift\Models\Shift;
use App\Support\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Refund penuh, maks 1 per order (BR-035). Tunai wajib punya shift_id (BR-012).
 */
class Refund extends Model
{
    use BelongsToBusiness;
    use HasOutletScope;

    protected $fillable = [
        'business_id', 'outlet_id', 'order_id', 'payment_id', 'shift_id', 'amount', 'method', 'reason',
        'restock', 'gateway_refund_key', 'gateway_status', 'refunded_by', 'refunded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'integer',
            'restock' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
