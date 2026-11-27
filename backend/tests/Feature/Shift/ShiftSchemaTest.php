<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\User\Models\User;
use App\Modules\Shift\Models\CashMovement;
use App\Modules\Shift\Models\Shift;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function shfSchemaShift(int $businessId, int $outletId, int $cashierId, array $extra = []): int
{
    return DB::table('shifts')->insertGetId(array_merge([
        'business_id' => $businessId, 'outlet_id' => $outletId, 'cashier_id' => $cashierId,
        'status' => 'OPEN', 'business_date' => '2026-11-02', 'opening_cash' => 200000, 'opened_at' => now(),
    ], $extra));
}

beforeEach(function () {
    $this->biz = Business::factory()->create()->id;
    $this->outlet1 = Outlet::factory()->create(['business_id' => $this->biz])->id;
    $this->outlet2 = Outlet::factory()->create(['business_id' => $this->biz])->id;
    $this->kasirA = User::factory()->create(['business_id' => $this->biz])->id;
    $this->kasirB = User::factory()->create(['business_id' => $this->biz])->id;
});

it('shift OPEN kedua milik kasir yang sama di outlet sama ditolak DB (AC1, BR-002)', function () {
    shfSchemaShift($this->biz, $this->outlet1, $this->kasirA);

    expect(fn () => DB::transaction(fn () => shfSchemaShift($this->biz, $this->outlet1, $this->kasirA)))
        ->toThrow(QueryException::class);
});

it('kasir lain di outlet sama, atau kasir sama di outlet lain, boleh punya shift OPEN', function () {
    shfSchemaShift($this->biz, $this->outlet1, $this->kasirA);

    expect(shfSchemaShift($this->biz, $this->outlet1, $this->kasirB))->toBeInt()
        ->and(shfSchemaShift($this->biz, $this->outlet2, $this->kasirA))->toBeInt();
});

it('setelah shift CLOSED, kasir boleh membuka shift OPEN baru (UNIQUE parsial)', function () {
    $first = shfSchemaShift($this->biz, $this->outlet1, $this->kasirA);
    DB::table('shifts')->where('id', $first)->update(['status' => 'CLOSED', 'closed_at' => now()]);

    expect(shfSchemaShift($this->biz, $this->outlet1, $this->kasirA))->toBeInt();
});

it('CHECK shifts menolak nilai di luar ERD', function (array $override) {
    expect(fn () => DB::transaction(fn () => shfSchemaShift($this->biz, $this->outlet1, $this->kasirA, $override)))
        ->toThrow(QueryException::class);
})->with([
    'status salah' => [['status' => 'PAUSED']],
    'kas awal negatif' => [['opening_cash' => -1]],
]);

it('cash_movements: INSERT boleh, UPDATE dan DELETE ditolak trigger (AC2)', function () {
    $shiftId = shfSchemaShift($this->biz, $this->outlet1, $this->kasirA);
    $id = DB::table('cash_movements')->insertGetId([
        'business_id' => $this->biz, 'shift_id' => $shiftId, 'type' => 'OUT',
        'amount' => 15000, 'reason' => 'Beli es batu', 'user_id' => $this->kasirA,
    ]);

    expect(fn () => DB::transaction(fn () => DB::table('cash_movements')->where('id', $id)->update(['amount' => 1])))
        ->toThrow(QueryException::class, 'append-only');

    expect(fn () => DB::transaction(fn () => DB::table('cash_movements')->where('id', $id)->delete()))
        ->toThrow(QueryException::class, 'append-only');

    expect(DB::table('cash_movements')->find($id)->amount)->toBe(15000);
});

it('CHECK cash_movements menolak nilai di luar ERD', function (array $override) {
    $shiftId = shfSchemaShift($this->biz, $this->outlet1, $this->kasirA);

    expect(fn () => DB::transaction(fn () => DB::table('cash_movements')->insert(array_merge([
        'business_id' => $this->biz, 'shift_id' => $shiftId, 'type' => 'IN',
        'amount' => 10000, 'reason' => 'Tambah kembalian', 'user_id' => $this->kasirA,
    ], $override))))->toThrow(QueryException::class);
})->with([
    'type salah' => [['type' => 'X']],
    'amount nol' => [['amount' => 0]],
    'amount negatif' => [['amount' => -5000]],
]);

it('cash_movements tidak punya updated_at dan shifts punya index ERD §4.1', function () {
    expect(Schema::hasColumn('cash_movements', 'updated_at'))->toBeFalse();

    $indexes = collect(DB::select("select indexname, indexdef from pg_indexes where tablename in ('shifts', 'cash_movements')"))
        ->pluck('indexdef', 'indexname');

    expect($indexes['shifts_one_open_per_cashier_unique'])->toContain('UNIQUE')->toContain('(outlet_id, cashier_id)')->toContain("'OPEN'")
        ->and($indexes['shifts_open_opened_at_index'])->toContain('(outlet_id, opened_at)')
        ->and($indexes['shifts_outlet_id_business_date_index'])->toContain('(outlet_id, business_date)')
        ->and($indexes['cash_movements_shift_id_type_index'])->toContain('(shift_id, type)');
});

it('trigger cash_movements terpasang', function () {
    $triggers = collect(DB::select("select tgname from pg_trigger where tgrelid = 'cash_movements'::regclass and not tgisinternal"))
        ->pluck('tgname');

    expect($triggers)->toContain('cash_movements_append_only');
});

it('model Shift dan CashMovement memakai BelongsToBusiness; factory konsisten satu business', function () {
    expect(class_uses_recursive(Shift::class))->toContain(BelongsToBusiness::class)
        ->and(class_uses_recursive(CashMovement::class))->toContain(BelongsToBusiness::class);

    $shift = Shift::factory()->create();

    expect($shift->outlet->business_id)->toBe($shift->business_id)
        ->and($shift->cashier->business_id)->toBe($shift->business_id);
});
