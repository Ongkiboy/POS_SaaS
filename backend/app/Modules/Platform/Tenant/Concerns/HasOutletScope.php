<?php

namespace App\Modules\Platform\Tenant\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Untuk model yang punya kolom outlet_id. Pemakaian: Model::query()->forOutlet($outletId).
 *
 * @phpstan-require-extends Model
 *
 * @mixin Model
 */
trait HasOutletScope
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForOutlet(Builder $query, int $outletId): Builder
    {
        // qualifyColumn milik Builder: tidak bergantung pada $this, jadi aman dibaca IDE dan Larastan.
        return $query->where($query->qualifyColumn('outlet_id'), $outletId);
    }
}
