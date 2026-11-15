<?php

namespace App\Modules\Shift\Models;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\Tenant\Concerns\HasOutletScope;
use App\Modules\Platform\User\Models\User;
use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<ShiftFactory> */
    use HasFactory;

    use HasOutletScope;

    protected $fillable = [
        'business_id', 'outlet_id', 'cashier_id', 'status', 'business_date', 'opening_cash',
        'expected_cash', 'actual_cash', 'difference', 'closed_by', 'opened_at', 'closed_at', 'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'opening_cash' => 'integer',
            'expected_cash' => 'integer',
            'actual_cash' => 'integer',
            'difference' => 'integer',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * @return HasMany<CashMovement, $this>
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    protected static function newFactory(): ShiftFactory
    {
        return ShiftFactory::new();
    }
}
