<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\IngredientUnitConversion;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Database\Seeders\UnitSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function ingSchemaIngredient(int $businessId, int $unitId, string $sku = 'SUSU-001', array $extra = []): int
{
    return DB::table('ingredients')->insertGetId(array_merge([
        'business_id' => $businessId, 'base_unit_id' => $unitId, 'sku' => $sku,
        'name' => 'Susu UHT', 'status' => 'ACTIVE',
    ], $extra));
}

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->biz = Business::factory()->create()->id;
    $this->gram = Unit::query()->where('code', 'g')->value('id');
});

it('UnitSeeder mengisi g, kg, ml, L, pcs dan idempoten (AC1)', function () {
    $this->seed(UnitSeeder::class);

    expect(Unit::query()->orderBy('code')->pluck('code')->all())
        ->toEqualCanonicalizing(['g', 'kg', 'ml', 'L', 'pcs'])
        ->and(Unit::query()->count())->toBe(5);
});

it('faktor baku kg→g dan L→ml = 1000 (AC1, OI-26 opsi a)', function () {
    expect(config('units.conversions.kg'))->toBe(['base' => 'g', 'factor' => '1000'])
        ->and(config('units.conversions.L'))->toBe(['base' => 'ml', 'factor' => '1000'])
        ->and(config('units.base_units'))->toBe(['g', 'ml', 'pcs']);
});

it('kolom last_cost bertipe numeric(18,4) dengan default 0 (AC2)', function () {
    $col = DB::selectOne(
        "select data_type, numeric_precision, numeric_scale, column_default from information_schema.columns where table_name = 'ingredients' and column_name = 'last_cost'"
    );

    expect($col->data_type)->toBe('numeric')
        ->and($col->numeric_precision)->toBe(18)
        ->and($col->numeric_scale)->toBe(4)
        ->and($col->column_default)->toContain('0');
});

it('CHECK ingredients menolak nilai di luar ERD (AC2)', function (array $override) {
    expect(fn () => DB::transaction(fn () => ingSchemaIngredient($this->biz, $this->gram, 'X-1', $override)))
        ->toThrow(QueryException::class);
})->with([
    'last_cost negatif' => [['last_cost' => '-0.0001']],
    'min_stock negatif' => [['min_stock' => '-1']],
    'status salah' => [['status' => 'ON']],
]);

it('last_cost menyimpan 4 desimal tanpa pembulatan float', function () {
    $id = ingSchemaIngredient($this->biz, $this->gram, 'KOPI-01', ['last_cost' => '12.3456']);

    expect(Ingredient::query()->find($id)->last_cost)->toBe('12.3456');
});

it('SKU bahan unik per business untuk baris aktif (TC-ING-001 bagian DB)', function () {
    $first = ingSchemaIngredient($this->biz, $this->gram, 'SUSU-001');

    expect(fn () => DB::transaction(fn () => ingSchemaIngredient($this->biz, $this->gram, 'SUSU-001')))
        ->toThrow(QueryException::class);

    $other = Business::factory()->create()->id;
    expect(ingSchemaIngredient($other, $this->gram, 'SUSU-001'))->toBeInt();

    DB::table('ingredients')->where('id', $first)->update(['deleted_at' => now()]);
    expect(ingSchemaIngredient($this->biz, $this->gram, 'SUSU-001'))->toBeInt();
});

it('konversi: satu baris per (ingredient, unit) dan faktor harus > 0', function () {
    $ingredientId = ingSchemaIngredient($this->biz, $this->gram);
    $kg = Unit::query()->where('code', 'kg')->value('id');
    $row = ['business_id' => $this->biz, 'ingredient_id' => $ingredientId, 'unit_id' => $kg, 'factor_to_base' => '1000'];
    DB::table('ingredient_unit_conversions')->insert($row);

    expect(fn () => DB::transaction(fn () => DB::table('ingredient_unit_conversions')->insert($row)))
        ->toThrow(QueryException::class);

    $ml = Unit::query()->where('code', 'ml')->value('id');
    expect(fn () => DB::transaction(fn () => DB::table('ingredient_unit_conversions')->insert(
        array_merge($row, ['unit_id' => $ml, 'factor_to_base' => '0'])
    )))->toThrow(QueryException::class);
});

it('FK RESTRICT: unit yang dipakai bahan tidak bisa dihapus', function () {
    ingSchemaIngredient($this->biz, $this->gram);

    expect(fn () => DB::transaction(fn () => DB::table('units')->where('id', $this->gram)->delete()))
        ->toThrow(QueryException::class);
});

it('index ERD §4.1 ingredients ada', function () {
    $indexes = collect(DB::select("select indexname, indexdef from pg_indexes where tablename = 'ingredients'"))
        ->pluck('indexdef', 'indexname');

    expect($indexes['ingredients_business_sku_unique'])->toContain('UNIQUE')->toContain('(business_id, sku)')->toContain('deleted_at IS NULL')
        ->and($indexes['ingredients_business_status_index'])->toContain('(business_id, status)');
});

it('model tenant memakai BelongsToBusiness, Unit tidak', function () {
    expect(class_uses_recursive(Ingredient::class))->toContain(BelongsToBusiness::class)
        ->and(class_uses_recursive(IngredientUnitConversion::class))->toContain(BelongsToBusiness::class)
        ->and(class_uses_recursive(Unit::class))->not->toContain(BelongsToBusiness::class);
});

it('IngredientFactory membuat bahan dengan base unit g', function () {
    $ingredient = Ingredient::factory()->create();

    expect($ingredient->baseUnit->code)->toBe('g')
        ->and($ingredient->last_cost)->toBe('0.0000');
});
