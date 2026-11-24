<?php

namespace App\Modules\Sales\Payment\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Sales\Order\Models\Order;
use App\Modules\Shift\Models\Shift;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentProvider;
use App\Support\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pembayaran berhasil (ERD §3.4). Append-only: trigger DB menolak UPDATE/DELETE (ADR-007).
 */
class Payment extends Model
{
    use BelongsToBusiness;
    use HasOutletScope;

    /** Tabel append-only tidak punya updated_at. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id', 'outlet_id', 'order_id', 'shift_id', 'method', 'provider', 'gateway_transaction_id',
        'gateway_payment_type', 'status', 'amount', 'tendered_amount', 'change_amount', 'reference_no',
        'idempotency_key', 'created_by', 'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'provider' => PaymentProvider::class,
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'tendered_amount' => 'integer',
            'change_amount' => 'integer',
            'paid_at' => 'datetime',
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
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
