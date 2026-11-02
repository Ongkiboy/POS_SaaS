<?php

use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Catalog\Modifier\Models\ModifierOption;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function catSchemaCategory(int $businessId, string $name = 'Kopi', array $extra = []): int
{
    return DB::table('categories')->insertGetId(array_merge([
        'business_id' => $businessId, 'name' => $name, 'status' => 'ACTIVE',
    ], $extra));
}

function catSchemaProduct(int $businessId, int $categoryId, string $sku = 'KOPI-001', array $extra = []): int
{
    return DB::table('products')->insertGetId(array_merge([
        'business_id' => $businessId, 'category_id' => $categoryId, 'sku' => $sku,
        'name' => 'Es Kopi Susu', 'selling_price' => 18000, 'status' => 'ACTIVE',
    ], $extra));
}

function catSchemaGroup(int $businessId, array $extra = []): int
{
    return DB::table('modifier_groups')->insertGetId(array_merge([
        'business_id' => $businessId, 'name' => 'Ukuran', 'selection_type' => 'SINGLE',
        'is_required' => true, 'min_select' => 1, 'max_select' => 1, 'status' => 'ACTIVE',
    ], $extra));
}

beforeEach(function () {
    $this->bizA = Business::factory()->create()->id;
    $this->bizB = Business::factory()->create()->id;
});

it('products tidak punya kolom cost_price (AC2)', function () {
    expect(Schema::hasColumn('products', 'cost_price'))->toBeFalse()
        ->and(Schema::hasColumn('products', 'selling_price'))->toBeTrue();
});

it('SKU produk unik per business untuk baris aktif (AC1, TC-CAT-001 bagian DB)', function () {
    $catA = catSchemaCategory($this->bizA);
    catSchemaProduct($this->bizA, $catA, 'KOPI-001');

    expect(fn () => DB::transaction(fn () => catSchemaProduct($this->bizA, $catA, 'KOPI-001')))
        ->toThrow(QueryException::class);
});

it('SKU yang sama boleh dipakai business lain', function () {
    catSchemaProduct($this->bizA, catSchemaCategory($this->bizA), 'KOPI-001');
    $idB = catSchemaProduct($this->bizB, catSchemaCategory($this->bizB), 'KOPI-001');

    expect($idB)->toBeInt();
});

it('SKU boleh dipakai ulang setelah produk lama di-soft-delete (UNIQUE parsial)', function () {
    $catA = catSchemaCategory($this->bizA);
    $old = catSchemaProduct($this->bizA, $catA, 'KOPI-001');
    DB::table('products')->where('id', $old)->update(['deleted_at' => now()]);

    expect(catSchemaProduct($this->bizA, $catA, 'KOPI-001'))->toBeInt();
});

it('nama kategori unik per business untuk baris aktif, boleh dipakai ulang setelah dihapus (AC1)', function () {
    $first = catSchemaCategory($this->bizA, 'Kopi');

    expect(fn () => DB::transaction(fn () => catSchemaCategory($this->bizA, 'Kopi')))
        ->toThrow(QueryException::class);

    catSchemaCategory($this->bizB, 'Kopi');
    DB::table('categories')->where('id', $first)->update(['deleted_at' => now()]);

    expect(catSchemaCategory($this->bizA, 'Kopi'))->toBeInt();
});

it('CHECK products menolak nilai di luar ERD', function (array $override) {
    $catA = catSchemaCategory($this->bizA);

    expect(fn () => DB::transaction(fn () => catSchemaProduct($this->bizA, $catA, 'X-1', $override)))
        ->toThrow(QueryException::class);
})->with([
    'harga negatif' => [['selling_price' => -1]],
    'status salah' => [['status' => 'DELETED']],
]);

it('CHECK categories menolak status di luar ACTIVE/INACTIVE', function () {
    expect(fn () => DB::transaction(fn () => catSchemaCategory($this->bizA, 'X', ['status' => 'ON'])))
        ->toThrow(QueryException::class);
});

it('CHECK modifier_groups menolak nilai di luar ERD', function (array $override) {
    expect(fn () => DB::transaction(fn () => catSchemaGroup($this->bizA, $override)))
        ->toThrow(QueryException::class);
})->with([
    'selection_type salah' => [['selection_type' => 'TRIPLE']],
    'min lebih besar dari max' => [['min_select' => 3, 'max_select' => 2]],
    'status salah' => [['status' => 'ON']],
]);

it('CHECK modifier_options menolak price_delta negatif', function () {
    $groupId = catSchemaGroup($this->bizA);

    expect(fn () => DB::transaction(fn () => DB::table('modifier_options')->insert([
        'business_id' => $this->bizA, 'modifier_group_id' => $groupId, 'name' => 'Large',
        'price_delta' => -1000, 'status' => 'ACTIVE',
    ])))->toThrow(QueryException::class);
});

it('grup modifier tidak bisa dipasang dua kali ke produk yang sama (PK komposit)', function () {
    $productId = catSchemaProduct($this->bizA, catSchemaCategory($this->bizA));
    $groupId = catSchemaGroup($this->bizA);
    $row = ['product_id' => $productId, 'modifier_group_id' => $groupId, 'business_id' => $this->bizA];
    DB::table('product_modifier_groups')->insert($row);

    expect(fn () => DB::transaction(fn () => DB::table('product_modifier_groups')->insert($row)))
        ->toThrow(QueryException::class);
});

it('FK RESTRICT menolak hapus kategori yang masih punya produk', function () {
    $catA = catSchemaCategory($this->bizA);
    catSchemaProduct($this->bizA, $catA);

    expect(fn () => DB::transaction(fn () => DB::table('categories')->where('id', $catA)->delete()))
        ->toThrow(QueryException::class);
});

it('index ERD §4.1 ada dengan kolom dan kondisi yang benar', function () {
    $indexes = collect(DB::select(
        "select indexname, indexdef from pg_indexes where tablename in ('categories', 'products', 'modifier_options', 'product_modifier_groups')"
    ))->pluck('indexdef', 'indexname');

    expect($indexes['categories_business_name_unique'])->toContain('UNIQUE')->toContain('(business_id, name)')->toContain('deleted_at IS NULL')
        ->and($indexes['products_business_sku_unique'])->toContain('UNIQUE')->toContain('(business_id, sku)')->toContain('deleted_at IS NULL')
        ->and($indexes['products_business_category_status_index'])->toContain('(business_id, category_id, status)')
        ->and($indexes['modifier_options_modifier_group_id_status_index'])->toContain('(modifier_group_id, status)')
        ->and($indexes['product_modifier_groups_modifier_group_id_index'])->toContain('(modifier_group_id)');
});

it('id memakai identity GENERATED ALWAYS', function () {
    $row = DB::selectOne(
        "select is_identity, identity_generation from information_schema.columns where table_name = 'products' and column_name = 'id'"
    );

    expect($row->is_identity)->toBe('YES')
        ->and($row->identity_generation)->toBe('ALWAYS');
});

it('semua model katalog memakai BelongsToBusiness', function () {
    foreach ([Category::class, Product::class, ModifierGroup::class, ModifierOption::class] as $model) {
        expect(class_uses_recursive($model))->toContain(BelongsToBusiness::class);
    }
});

it('factory membuat produk dan kategori di business yang sama, dan relasi modifier mengisi business_id pivot', function () {
    $product = Product::factory()->create();
    $group = ModifierGroup::query()->create([
        'business_id' => $product->business_id, 'name' => 'Gula', 'selection_type' => 'SINGLE',
        'is_required' => false, 'min_select' => 0, 'max_select' => 1, 'status' => 'ACTIVE',
    ]);
    $product->modifierGroups()->attach($group->id, ['business_id' => $product->business_id]);

    expect($product->category->business_id)->toBe($product->business_id)
        ->and($product->modifierGroups()->count())->toBe(1);
});
