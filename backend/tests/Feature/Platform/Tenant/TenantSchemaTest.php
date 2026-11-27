<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function tenantSchemaBusiness(string $slug): int
{
    return DB::table('businesses')->insertGetId(['name' => $slug, 'slug' => $slug, 'status' => 'ACTIVE']);
}

function tenantSchemaOutlet(int $businessId, string $code, array $extra = []): int
{
    return DB::table('outlets')->insertGetId(array_merge([
        'business_id' => $businessId, 'code' => $code, 'name' => 'Outlet '.$code, 'status' => 'ACTIVE',
    ], $extra));
}

function tenantSchemaUser(int $businessId, string $email): int
{
    return DB::table('users')->insertGetId([
        'business_id' => $businessId, 'name' => 'U', 'email' => $email, 'password' => 'x', 'status' => 'ACTIVE',
    ]);
}

it('outlet memakai default ERD §3.1', function () {
    $outletId = tenantSchemaOutlet(tenantSchemaBusiness('b1'), 'KP01');
    $outlet = DB::table('outlets')->find($outletId);

    expect($outlet->timezone)->toBe('Asia/Jakarta')
        ->and($outlet->tax_rate)->toBe('0.00')
        ->and($outlet->service_charge_rate)->toBe('0.00')
        ->and($outlet->cashier_max_discount_percent)->toBe('10.00')
        ->and($outlet->rounding_increment)->toBe(100)
        ->and($outlet->allow_negative_stock)->toBeTrue()
        ->and($outlet->created_at)->not->toBeNull();
});

it('kode outlet unik per business, boleh sama di business lain (TC-TEN-003)', function () {
    $a = tenantSchemaBusiness('a');
    $b = tenantSchemaBusiness('b');
    tenantSchemaOutlet($a, 'KP01');
    tenantSchemaOutlet($b, 'KP01');

    expect(fn () => DB::transaction(fn () => tenantSchemaOutlet($a, 'KP01')))->toThrow(QueryException::class);
});

it('email user unik global dan case-insensitive', function () {
    $a = tenantSchemaBusiness('a');
    $b = tenantSchemaBusiness('b');
    tenantSchemaUser($a, 'owner@kopi.test');

    expect(fn () => DB::transaction(fn () => tenantSchemaUser($b, 'OWNER@kopi.test')))->toThrow(QueryException::class);
});

it('slug business unik', function () {
    tenantSchemaBusiness('kopi-pagi');

    expect(fn () => DB::transaction(fn () => tenantSchemaBusiness('kopi-pagi')))->toThrow(QueryException::class);
});

it('CHECK outlet menolak nilai di luar batas ERD', function (array $override) {
    $businessId = tenantSchemaBusiness('x');

    expect(fn () => DB::transaction(fn () => tenantSchemaOutlet($businessId, 'X1', $override)))
        ->toThrow(QueryException::class);
})->with([
    'rounding_increment 250' => [['rounding_increment' => 250]],
    'tax_rate 100.01' => [['tax_rate' => '100.01']],
    'service_charge_rate -1' => [['service_charge_rate' => '-1']],
    'cashier_max_discount_percent 101' => [['cashier_max_discount_percent' => '101']],
    'status salah' => [['status' => 'DELETED']],
]);

it('CHECK status businesses dan users menolak nilai di luar ACTIVE/INACTIVE', function () {
    expect(fn () => DB::transaction(fn () => DB::table('businesses')->insert(['name' => 'n', 'slug' => 'x6', 'status' => 'ON'])))
        ->toThrow(QueryException::class);

    $businessId = tenantSchemaBusiness('x7');

    expect(fn () => DB::transaction(fn () => DB::table('users')->insert([
        'business_id' => $businessId, 'name' => 'n', 'email' => 'x7@t.test', 'password' => 'x', 'status' => 'BANNED',
    ])))->toThrow(QueryException::class);
});

it('FK ON DELETE RESTRICT menolak hapus business yang punya outlet', function () {
    $a = tenantSchemaBusiness('a');
    tenantSchemaOutlet($a, 'KP01');

    expect(fn () => DB::transaction(fn () => DB::table('businesses')->where('id', $a)->delete()))->toThrow(QueryException::class);
});

it('id memakai identity GENERATED ALWAYS', function () {
    $row = DB::selectOne(
        "select is_identity, identity_generation from information_schema.columns where table_name = 'outlets' and column_name = 'id'"
    );

    expect($row->is_identity)->toBe('YES')
        ->and($row->identity_generation)->toBe('ALWAYS');
});
