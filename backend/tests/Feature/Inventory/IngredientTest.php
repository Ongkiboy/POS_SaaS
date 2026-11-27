<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\IngredientUnitConversion;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Platform\Business\Models\Business;
use Database\Seeders\UnitSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = rbacSeededBusiness();
    $this->manager = rbacUserWithRole($this->business, 'MANAGER');
    $this->inventoryUser = rbacUserWithRole($this->business, 'INVENTORY');
    $this->cashier = rbacUserWithRole($this->business, 'CASHIER');
    $this->kitchen = rbacUserWithRole($this->business, 'KITCHEN');

    $this->gram = Unit::query()->where('code', 'g')->firstOrFail();
    $this->kg = Unit::query()->where('code', 'kg')->firstOrFail();
    $this->ml = Unit::query()->where('code', 'ml')->firstOrFail();
    $this->liter = Unit::query()->where('code', 'L')->firstOrFail();
    $this->pcs = Unit::query()->where('code', 'pcs')->firstOrFail();
});

it('POST /ingredients membuat bahan dan konversi otomatis (BE-ING-001)', function () {
    $payload = [
        'sku' => 'SUSU-001',
        'name' => 'Susu Segar',
        'base_unit_id' => $this->ml->id,
        'last_cost' => '15.5000',
        'min_stock' => '1000.0000',
        'status' => 'ACTIVE',
    ];

    $response = $this->actingAs($this->inventoryUser)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.sku', 'SUSU-001')
        ->assertJsonPath('data.name', 'Susu Segar')
        ->assertJsonPath('data.base_unit.code', 'ml')
        ->assertJsonPath('data.last_cost', '15.5000')
        ->assertJsonPath('data.min_stock', '1000.0000')
        ->assertJsonPath('data.status', 'ACTIVE');

    $ingredientId = $response->json('data.id');

    // Cek konversi otomatis: ml (1) dan L (1000)
    $conversions = IngredientUnitConversion::query()
        ->where('ingredient_id', $ingredientId)
        ->get();

    expect($conversions)->toHaveCount(2);

    $mlConv = $conversions->firstWhere('unit_id', $this->ml->id);
    $lConv = $conversions->firstWhere('unit_id', $this->liter->id);

    expect($mlConv)->not->toBeNull()
        ->and((string) $mlConv->factor_to_base)->toBe('1.000000')
        ->and($lConv)->not->toBeNull()
        ->and((string) $lConv->factor_to_base)->toBe('1000.000000');
});

it('POST /ingredients membuat konversi g=1 dan kg=1000 untuk base unit gram', function () {
    $payload = [
        'sku' => 'KOPI-001',
        'name' => 'Biji Kopi Arabika',
        'base_unit_id' => $this->gram->id,
        'last_cost' => '250.0000',
    ];

    $response = $this->actingAs($this->manager)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertCreated();

    $ingredientId = $response->json('data.id');

    $conversions = IngredientUnitConversion::query()
        ->where('ingredient_id', $ingredientId)
        ->get();

    expect($conversions)->toHaveCount(2);

    $gConv = $conversions->firstWhere('unit_id', $this->gram->id);
    $kgConv = $conversions->firstWhere('unit_id', $this->kg->id);

    expect((string) $gConv->factor_to_base)->toBe('1.000000')
        ->and((string) $kgConv->factor_to_base)->toBe('1000.000000');
});

it('POST /ingredients menolak SKU ganda di business yang sama dengan 422 (AC1)', function () {
    $payload = [
        'sku' => 'GULA-001',
        'name' => 'Gula Pasir',
        'base_unit_id' => $this->gram->id,
    ];

    $this->actingAs($this->manager)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertCreated();

    $this->actingAs($this->manager)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.sku.0', 'SKU bahan sudah digunakan.');
});

it('POST /ingredients menolak last_cost negatif dengan 422 (AC2)', function () {
    $payload = [
        'sku' => 'TEH-001',
        'name' => 'Teh Melati',
        'base_unit_id' => $this->gram->id,
        'last_cost' => '-100',
    ];

    $this->actingAs($this->manager)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.last_cost.0', 'Biaya per satuan tidak boleh negatif.');
});

it('POST /ingredients menolak unit yang bukan base unit (mis. kg atau L)', function () {
    $payload = [
        'sku' => 'TEH-002',
        'name' => 'Teh Celup',
        'base_unit_id' => $this->kg->id,
    ];

    $this->actingAs($this->manager)
        ->postJson('/api/v1/ingredients', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('GET /ingredients menampilkan daftar bahan terpaginasi untuk tenant aktif', function () {
    Ingredient::factory()->for($this->business)->create(['name' => 'Bahan A', 'sku' => 'A-01', 'base_unit_id' => $this->gram->id]);
    Ingredient::factory()->for($this->business)->create(['name' => 'Bahan B', 'sku' => 'B-01', 'base_unit_id' => $this->gram->id]);

    $response = $this->actingAs($this->kitchen)
        ->getJson('/api/v1/ingredients')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

it('GET /ingredients/{id} mengembalikan detail bahan beserta baseUnit dan conversions', function () {
    $ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'SIRUP-01',
        'name' => 'Sirup Karamel',
        'base_unit_id' => $this->ml->id,
    ]);

    $this->actingAs($this->inventoryUser)
        ->getJson("/api/v1/ingredients/{$ingredient->id}")
        ->assertOk()
        ->assertJsonPath('data.sku', 'SIRUP-01')
        ->assertJsonPath('data.base_unit.code', 'ml');
});

it('PUT /ingredients/{id} dapat mengubah data bahan', function () {
    $ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'SIRUP-02',
        'name' => 'Sirup Vanila',
        'base_unit_id' => $this->ml->id,
        'last_cost' => '10.0000',
    ]);

    $this->actingAs($this->manager)
        ->putJson("/api/v1/ingredients/{$ingredient->id}", [
            'sku' => 'SIRUP-02',
            'name' => 'Sirup Vanila Premium',
            'base_unit_id' => $this->ml->id,
            'last_cost' => '12.0000',
            'status' => 'ACTIVE',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Sirup Vanila Premium')
        ->assertJsonPath('data.last_cost', '12.0000');
});

it('PUT /ingredients/{id} menolak ubah base unit jika sudah tercatat movement (AC1)', function () {
    $ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'BERAS-01',
        'name' => 'Beras Organik',
        'base_unit_id' => $this->gram->id,
    ]);

    // Simulasi tabel stock_movements jika ada
    if (! Schema::hasTable('stock_movements')) {
        Schema::create('stock_movements', function ($table) {
            $table->id();
            $table->foreignId('ingredient_id');
        });
    }

    DB::table('stock_movements')->insert([
        'ingredient_id' => $ingredient->id,
    ]);

    $this->actingAs($this->manager)
        ->putJson("/api/v1/ingredients/{$ingredient->id}", [
            'sku' => 'BERAS-01',
            'name' => 'Beras Organik',
            'base_unit_id' => $this->pcs->id,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.base_unit_id.0', 'Satuan dasar tidak dapat diubah karena stok sudah tercatat.');
});

it('DELETE /ingredients/{id} melakukan soft delete', function () {
    $ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'HAPUS-01',
        'name' => 'Bahan Akan Dihapus',
        'base_unit_id' => $this->gram->id,
    ]);

    $this->actingAs($this->manager)
        ->deleteJson("/api/v1/ingredients/{$ingredient->id}")
        ->assertOk();

    expect(Ingredient::query()->find($ingredient->id))->toBeNull();
    expect(Ingredient::withTrashed()->find($ingredient->id))->not->toBeNull();
});

it('data business lain mengembalikan 404 NOT_FOUND (isolasi tenant)', function () {
    $otherBusiness = rbacSeededBusiness();
    $otherIngredient = Ingredient::factory()->for($otherBusiness)->create([
        'sku' => 'OTHER-01',
        'name' => 'Bahan Business Lain',
        'base_unit_id' => $this->gram->id,
    ]);

    // User dari business utama mencoba akses ingredient business lain -> 404
    $this->actingAs($this->manager)
        ->getJson("/api/v1/ingredients/{$otherIngredient->id}")
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');

    $this->actingAs($this->manager)
        ->putJson("/api/v1/ingredients/{$otherIngredient->id}", [
            'sku' => 'OTHER-01',
            'name' => 'Hacked',
            'base_unit_id' => $this->gram->id,
        ])
        ->assertNotFound();

    $this->actingAs($this->manager)
        ->deleteJson("/api/v1/ingredients/{$otherIngredient->id}")
        ->assertNotFound();
});

it('role tanpa permission mengembalikan 403 FORBIDDEN', function () {
    $this->actingAs($this->cashier)
        ->getJson('/api/v1/ingredients')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');

    $this->actingAs($this->kitchen)
        ->postJson('/api/v1/ingredients', [
            'sku' => 'FORBID-01',
            'name' => 'Terlarang',
            'base_unit_id' => $this->gram->id,
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});
