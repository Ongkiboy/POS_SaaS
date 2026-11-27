<?php

namespace App\Modules\Platform\Outlet\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\User\Models\User;
use Database\Factories\OutletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use BelongsToBusiness;

    /** @use HasFactory<OutletFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'business_id', 'code', 'name', 'address', 'timezone', 'tax_rate', 'service_charge_rate',
        'allow_negative_stock', 'cashier_max_discount_percent', 'rounding_increment',
        'receipt_header', 'receipt_footer', 'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'cashier_max_discount_percent' => 'decimal:2',
            'allow_negative_stock' => 'boolean',
            'rounding_increment' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets')->withPivot('business_id', 'created_at');
    }

    protected static function newFactory(): OutletFactory
    {
        return OutletFactory::new();
    }
}
