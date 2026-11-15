<?php

use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Catalog\Modifier\Models\ModifierOption;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Recipe\Models\ModifierOptionRecipeItem;
use App\Modules\Inventory\Recipe\Models\Recipe;
use App\Modules\Inventory\Recipe\Models\RecipeItem;
use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function recSchemaRecipe(int $businessId, int $productId, int $userId, int $version, bool $active): int
{
    return DB::table('recipes')->insertGetId([
        'business_id' => $businessId, 'product_id' => $productId, 'version' => $version,
        'is_active' => $active, 'created_by' => $userId,
    ]);
}

beforeEach(function () {
    $product = Product::factory()->create();
    $this->biz = $product->business_id;
    $this->product = $product->id;
    $this->user = User::factory()->create(['business_id' => $this->biz])->id;
    $this->kopi = Ingredient::factory()->create(['business_id' => $this->biz])->id;
    $this->susu = Ingredient::factory()->create(['business_id' => $this->biz])->id;
});

it('hanya satu recipe aktif per produk (AC1, BR-013)', function () {
    recSchemaRecipe($this->biz, $this->product, $this->user, 1, true);

    expect(fn () => DB::transaction(fn () => recSchemaRecipe($this->biz, $this->product, $this->user, 2, true)))
        ->toThrow(QueryException::class);
});

it('versi lama nonaktif boleh banyak, lalu versi baru boleh aktif', function () {
    $v1 = recSchemaRecipe($this->biz, $this->product, $this->user, 1, true);
    DB::table('recipes')->where('id', $v1)->update(['is_active' => false]);
    recSchemaRecipe($this->biz, $this->product, $this->user, 2, false);

    expect(recSchemaRecipe($this->biz, $this->product, $this->user, 3, true))->toBeInt();
});

it('nomor versi unik per produk', function () {
    recSchemaRecipe($this->biz, $this->product, $this->user, 1, false);

    expect(fn () => DB::transaction(fn () => recSchemaRecipe($this->biz, $this->product, $this->user, 1, false)))
        ->toThrow(QueryException::class);
});

it('satu bahan hanya sekali per recipe (AC2)', function () {
    $recipe = recSchemaRecipe($this->biz, $this->product, $this->user, 1, true);
    $row = ['business_id' => $this->biz, 'recipe_id' => $recipe, 'ingredient_id' => $this->kopi, 'quantity' => '18'];
    DB::table('recipe_items')->insert($row);
    DB::table('recipe_items')->insert(array_merge($row, ['ingredient_id' => $this->susu, 'quantity' => '150']));

    expect(fn () => DB::transaction(fn () => DB::table('recipe_items')->insert($row)))
        ->toThrow(QueryException::class);
});

it('quantity recipe harus > 0', function (string $qty) {
    $recipe = recSchemaRecipe($this->biz, $this->product, $this->user, 1, true);

    expect(fn () => DB::transaction(fn () => DB::table('recipe_items')->insert([
        'business_id' => $this->biz, 'recipe_id' => $recipe, 'ingredient_id' => $this->kopi, 'quantity' => $qty,
    ])))->toThrow(QueryException::class);
})->with(['nol' => ['0'], 'negatif' => ['-1.5']]);

it('modifier_option_recipe_items: bahan unik per option dan quantity > 0', function () {
    $group = ModifierGroup::query()->create([
        'business_id' => $this->biz, 'name' => 'Extra', 'selection_type' => 'MULTI',
        'is_required' => false, 'min_select' => 0, 'max_select' => 3, 'status' => 'ACTIVE',
    ]);
    $option = ModifierOption::query()->create([
        'business_id' => $this->biz, 'modifier_group_id' => $group->id, 'name' => 'Extra Shot',
        'price_delta' => 5000, 'status' => 'ACTIVE',
    ]);
    $row = ['business_id' => $this->biz, 'modifier_option_id' => $option->id, 'ingredient_id' => $this->kopi, 'quantity' => '9'];
    DB::table('modifier_option_recipe_items')->insert($row);

    expect(fn () => DB::transaction(fn () => DB::table('modifier_option_recipe_items')->insert($row)))
        ->toThrow(QueryException::class);

    expect(fn () => DB::transaction(fn () => DB::table('modifier_option_recipe_items')->insert(
        array_merge($row, ['ingredient_id' => $this->susu, 'quantity' => '0'])
    )))->toThrow(QueryException::class);
});

it('FK RESTRICT: produk yang punya recipe tidak bisa dihapus permanen', function () {
    recSchemaRecipe($this->biz, $this->product, $this->user, 1, true);

    expect(fn () => DB::transaction(fn () => DB::table('products')->where('id', $this->product)->delete()))
        ->toThrow(QueryException::class);
});

it('index ERD §4.1 recipe ada', function () {
    $indexes = collect(DB::select(
        "select indexname, indexdef from pg_indexes where tablename in ('recipes', 'recipe_items', 'modifier_option_recipe_items')"
    ))->pluck('indexdef', 'indexname');

    expect($indexes['recipes_one_active_per_product_unique'])->toContain('UNIQUE')->toContain('(product_id)')->toContain('is_active')
        ->and($indexes['recipes_product_id_version_unique'])->toContain('(product_id, version)')
        ->and($indexes['recipe_items_recipe_id_ingredient_id_unique'])->toContain('(recipe_id, ingredient_id)')
        ->and($indexes['recipe_items_ingredient_id_index'])->toContain('(ingredient_id)')
        ->and($indexes['mori_option_ingredient_unique'])->toContain('(modifier_option_id, ingredient_id)')
        ->and($indexes['modifier_option_recipe_items_ingredient_id_index'])->toContain('(ingredient_id)');
});

it('model recipe memakai BelongsToBusiness dan quantity dibaca 4 desimal', function () {
    foreach ([Recipe::class, RecipeItem::class, ModifierOptionRecipeItem::class] as $model) {
        expect(class_uses_recursive($model))->toContain(BelongsToBusiness::class);
    }

    $recipe = Recipe::query()->create([
        'business_id' => $this->biz, 'product_id' => $this->product, 'version' => 1,
        'is_active' => true, 'created_by' => $this->user,
    ]);
    $item = $recipe->items()->create(['business_id' => $this->biz, 'ingredient_id' => $this->kopi, 'quantity' => '18.5']);

    expect($item->fresh()->quantity)->toBe('18.5000')
        ->and($recipe->product->id)->toBe($this->product);
});
