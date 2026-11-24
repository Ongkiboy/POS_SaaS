<?php

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\TenantContext;
use App\Modules\Sales\Order\Services\OrderNumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Panggil generator seperti Action nanti: dalam transaksi + konteks tenant.
 */
function ordNumber(Outlet $outlet, string $businessDate): string
{
    return app(TenantContext::class)->runAs(
        $outlet->business_id,
        fn () => DB::transaction(fn () => app(OrderNumberGenerator::class)->next($outlet, $businessDate)),
    );
}

beforeEach(function () {
    $this->kp01 = Outlet::factory()->create(['code' => 'KP01']);
});

it('nomor ikut rollback bila transaksi pemanggil gagal', function () {
    $outlet = $this->kp01;

    try {
        app(TenantContext::class)->runAs($outlet->business_id, fn () => DB::transaction(function () use ($outlet) {
            app(OrderNumberGenerator::class)->next($outlet, '2026-11-23');
            throw new RuntimeException('order gagal dibuat');
        }));
    } catch (RuntimeException) {
        // diharapkan
    }

    expect(ordNumber($outlet, '2026-11-23'))->toBe('KP01-261123-0001');
});

it('format {KODE}-{YYMMDD}-{urut 4 digit}, berurutan', function () {
    expect(ordNumber($this->kp01, '2026-11-23'))->toBe('KP01-261123-0001')
        ->and(ordNumber($this->kp01, '2026-11-23'))->toBe('KP01-261123-0002')
        ->and(ordNumber($this->kp01, '2026-11-23'))->toBe('KP01-261123-0003');
});

it('AC1: reset per business date', function () {
    ordNumber($this->kp01, '2026-11-23');
    ordNumber($this->kp01, '2026-11-23');

    expect(ordNumber($this->kp01, '2026-11-24'))->toBe('KP01-261124-0001')
        ->and(ordNumber($this->kp01, '2026-11-23'))->toBe('KP01-261123-0003');
});

it('AC1: urutan terpisah per outlet', function () {
    $kp02 = Outlet::factory()->create(['business_id' => $this->kp01->business_id, 'code' => 'KP02']);
    ordNumber($this->kp01, '2026-11-23');

    expect(ordNumber($kp02, '2026-11-23'))->toBe('KP02-261123-0001');
});

it('AC2 (bagian sinkron): 50 nomor berturut-turut tanpa duplikat', function () {
    $numbers = [];

    for ($i = 0; $i < 50; $i++) {
        $numbers[] = ordNumber($this->kp01, '2026-11-23');
    }

    expect(array_unique($numbers))->toHaveCount(50)
        ->and(end($numbers))->toBe('KP01-261123-0050')
        ->and(DB::table('order_sequences')->where('outlet_id', $this->kp01->id)->value('last_number'))->toEqual(50);
});

it('mengunci baris order_sequences dengan FOR UPDATE', function () {
    DB::enableQueryLog();
    ordNumber($this->kp01, '2026-11-23');

    $sql = collect(DB::getQueryLog())->pluck('query')->implode("\n");

    expect(strtolower($sql))->toContain('for update');
});

it('menolak business_date yang bukan tanggal Y-m-d', function (string $date) {
    expect(fn () => ordNumber($this->kp01, $date))->toThrow(InvalidArgumentException::class);
})->with(['format lain' => ['23-11-2026'], 'tanggal mustahil' => ['2026-02-30'], 'dengan jam' => ['2026-11-23 10:00:00']]);
