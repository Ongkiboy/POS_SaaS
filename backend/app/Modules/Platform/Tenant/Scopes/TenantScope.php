<?php

namespace App\Modules\Platform\Tenant\Scopes;

use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope generik: tipe model mengikuti model yang memasangnya (stub Larastan untuk Scope bersifat generik).
 *
 * @template TModel of Model
 *
 * @implements Scope<TModel>
 */
final class TenantScope implements Scope
{
    /**
     * @param  Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $businessId = app(TenantContext::class)->id();

        if ($businessId === null) {
            return;
        }

        $builder->where($model->qualifyColumn('business_id'), $businessId);
    }
}
