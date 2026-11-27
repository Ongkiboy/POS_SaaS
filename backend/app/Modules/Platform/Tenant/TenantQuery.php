<?php

namespace App\Modules\Platform\Tenant;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur DB::table() untuk tabel tenant (TD §2.2). Gagal bila konteks kosong.
 */
final class TenantQuery
{
    public static function table(string $table): Builder
    {
        return DB::table($table)->where($table.'.business_id', app(TenantContext::class)->requireId());
    }
}
