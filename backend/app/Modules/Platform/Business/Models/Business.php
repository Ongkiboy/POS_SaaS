<?php

namespace App\Modules\Platform\Business\Models;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabel induk tenant: tidak memakai BelongsToBusiness (tidak punya business_id).
 */
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'address', 'license_plan', 'license_valid_until'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['license_valid_until' => 'date'];
    }

    /**
     * @return HasMany<Outlet, $this>
     */
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected static function newFactory(): BusinessFactory
    {
        return BusinessFactory::new();
    }
}
