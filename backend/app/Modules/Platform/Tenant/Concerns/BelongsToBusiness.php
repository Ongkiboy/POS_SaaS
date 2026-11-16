<?php

namespace App\Modules\Platform\Tenant\Concerns;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Tenant\Scopes\TenantScope;
use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Wajib dipakai semua model yang tabelnya punya kolom business_id (ADR-003, BR-022).
 *
 * Trait ini hanya boleh dipakai subclass Eloquent Model:
 * - @phpstan-require-extends: Larastan tahu $this/static adalah Model dan menolak class non-Model yang memakainya.
 * - @mixin: IDE (Intelephense/PhpStorm) mengenali method Model (addGlobalScope, creating, belongsTo, ...) di dalam trait.
 *
 * @phpstan-require-extends Model
 *
 * @mixin Model
 */
trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('business_id') === null) {
                $model->setAttribute('business_id', app(TenantContext::class)->requireId());
            }
        });
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
