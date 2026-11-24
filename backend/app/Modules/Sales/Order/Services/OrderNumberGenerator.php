<?php

namespace App\Modules\Sales\Order\Services;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\TenantContext;
use App\Modules\Platform\Tenant\TenantQuery;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

/**
 * Nomor order {KODE_OUTLET}-{YYMMDD}-{urut 4 digit} (FR-ORD-007, BR-019).
 *
 * - Urutan per outlet per business date; tanggal berikutnya mulai dari 1 lagi.
 * - business_date diambil dari shift (sudah dihitung menurut timezone outlet saat shift dibuka),
 *   jadi class ini tidak menghitung timezone sendiri.
 * - Aman konkurensi: baris order_sequences dikunci FOR UPDATE. Wajib dipanggil di dalam
 *   DB::transaction milik Action (TD §2.2: Service tidak membuka transaksi sendiri), supaya kunci
 *   dilepas bersamaan dengan commit order. Kalau order gagal dibuat, nomornya ikut rollback.
 */
final class OrderNumberGenerator
{
    public function next(Outlet $outlet, string $businessDate): string
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('OrderNumberGenerator wajib dipanggil di dalam DB::transaction.');
        }

        if (preg_match('/^(\d{2})(\d{2})-(\d{2})-(\d{2})$/', $businessDate, $d) !== 1
            || ! checkdate((int) $d[3], (int) $d[4], (int) ($d[1].$d[2]))) {
            throw new InvalidArgumentException("business_date harus tanggal Y-m-d: {$businessDate}");
        }

        $key = ['outlet_id' => $outlet->getKey(), 'business_date' => $businessDate];

        if ($outlet->business_id !== app(TenantContext::class)->requireId()) {
            throw new LogicException('Outlet bukan milik business pada konteks tenant.');
        }

        // 1. Pastikan barisnya ada. Dua request bersamaan: yang kedua menunggu, lalu diabaikan (ON CONFLICT DO NOTHING).
        TenantQuery::table('order_sequences')->insertOrIgnore($key + [
            'business_id' => $outlet->business_id,
            'last_number' => 0,
        ]);

        // 2. Kunci baris, naikkan, simpan. Request lain di outlet+tanggal yang sama antre di sini.
        $last = TenantQuery::table('order_sequences')->where($key)->lockForUpdate()->value('last_number');

        if ($last === null) {
            throw new LogicException('Baris order_sequences tidak ditemukan setelah insert.');
        }

        $number = (int) $last + 1;

        TenantQuery::table('order_sequences')->where($key)->update([
            'last_number' => $number,
            'updated_at' => now(),
        ]);

        // %04d: 0001–9999. Order ke-10.000 dalam sehari menjadi 5 digit (tidak terpotong).
        return sprintf('%s-%s%s%s-%04d', $outlet->code, $d[2], $d[3], $d[4], $number);
    }
}
