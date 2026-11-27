<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Inventory\Stock\Models\StockAdjustment;
use App\Modules\Inventory\Stock\Models\StockBalance;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Inventory\Stock\Models\StockOpeningBatch;
use App\Modules\Inventory\Waste\Models\WasteRecord;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use Database\Seeders\UnitSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = Business::factory()->create();
    $this->outlet = Outlet::factory()->create(['business_id' => $this->business->id]);
    $this->user = User::factory()->create(['business_id' => $this->business->id]);
    $this->gram = Unit::query()->where('code', 'g')->firstOrFail();

    $this->ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'KOPI-01',
        'name' => 'Biji Kopi Arabika',
        'base_unit_id' => $this->gram->id,
        'last_cost' => 150,
    ]);
});

it('tabel-tabel inventory dibuat lengkap dengan kolom ERD §3.3', function () {
    expect(Schema::hasTable('stock_balances'))->toBeTrue()
        ->and(Schema::hasTable('stock_opening_batches'))->toBeTrue()
        ->and(Schema::hasTable('stock_adjustments'))->toBeTrue()
        ->and(Schema::hasTable('waste_records'))->toBeTrue()
        ->and(Schema::hasTable('stock_movements'))->toBeTrue();
});

it('stock_balances menerapkan UNIQUE (outlet_id, ingredient_id) dan saldo boleh negatif', function () {
    $balance = StockBalance::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => -5.5000,
        'min_stock' => 10.0000,
    ]);

    expect($balance->quantity)->toBe('-5.5000')
        ->and($balance->min_stock)->toBe('10.0000');

    expect(fn () => StockBalance::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 10,
    ]))->toThrow(QueryException::class);
});

it('stock_balances menolak min_stock negatif', function () {
    expect(fn () => StockBalance::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 0,
        'min_stock' => -1,
    ]))->toThrow(QueryException::class);
});

it('stock_opening_batches mencatat opening stock batch', function () {
    $batch = StockOpeningBatch::create([
        'outlet_id' => $this->outlet->id,
        'user_id' => $this->user->id,
        'note' => 'Stok awal pembukaan cabang',
    ]);

    expect($batch->id)->toBeInt()
        ->and($batch->outlet->id)->toBe($this->outlet->id)
        ->and($batch->user->id)->toBe($this->user->id);
});

it('stock_adjustments mencatat penyesuaian stok fisik', function () {
    $adj = StockAdjustment::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'opname_item_id' => null,
        'quantity_before' => 10.0000,
        'quantity_delta' => -2.0000,
        'quantity_after' => 8.0000,
        'reason' => 'Koreksi hitungan manual',
        'user_id' => $this->user->id,
    ]);

    expect($adj->id)->toBeInt()
        ->and($adj->quantity_delta)->toBe('-2.0000')
        ->and($adj->reason)->toBe('Koreksi hitungan manual');
});

it('waste_records menolak quantity <= 0, reason tidak valid, dan reason OTHER tanpa note', function () {
    // Valid insert
    $waste = WasteRecord::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 2.5000,
        'reason' => 'EXPIRED',
        'note' => 'Kedaluwarsa',
        'unit_cost' => 150.0000,
        'total_cost' => 375,
        'user_id' => $this->user->id,
        'wasted_at' => now(),
    ]);
    expect($waste->id)->toBeInt();

    // Qty <= 0
    expect(fn () => WasteRecord::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 0,
        'reason' => 'EXPIRED',
        'unit_cost' => 150,
        'total_cost' => 0,
        'user_id' => $this->user->id,
        'wasted_at' => now(),
    ]))->toThrow(QueryException::class);

    // Reason tidak valid
    expect(fn () => WasteRecord::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 1,
        'reason' => 'INVALID_REASON',
        'unit_cost' => 150,
        'total_cost' => 150,
        'user_id' => $this->user->id,
        'wasted_at' => now(),
    ]))->toThrow(QueryException::class);

    // Reason OTHER tanpa note
    expect(fn () => WasteRecord::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'quantity' => 1,
        'reason' => 'OTHER',
        'note' => null,
        'unit_cost' => 150,
        'total_cost' => 150,
        'user_id' => $this->user->id,
        'wasted_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('stock_movements menegakkan UNIQUE idempotensi movement (AC1, BR-009)', function () {
    StockMovement::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'type' => 'OPENING',
        'quantity_delta' => 100.0000,
        'quantity_before' => 0.0000,
        'quantity_after' => 100.0000,
        'unit_cost' => 150.0000,
        'reference_type' => 'OPENING',
        'reference_id' => 1,
        'user_id' => $this->user->id,
        'occurred_at' => now(),
    ]);

    expect(fn () => StockMovement::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'type' => 'OPENING',
        'quantity_delta' => 100.0000,
        'quantity_before' => 0.0000,
        'quantity_after' => 100.0000,
        'unit_cost' => 150.0000,
        'reference_type' => 'OPENING',
        'reference_id' => 1,
        'user_id' => $this->user->id,
        'occurred_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('stock_movements menegakkan CHECK arah delta sesuai ERD §3.3 (AC1)', function (string $type, float $delta, bool $shouldPass) {
    $action = fn () => StockMovement::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'type' => $type,
        'quantity_delta' => $delta,
        'quantity_before' => 50.0000,
        'quantity_after' => 50.0000 + $delta,
        'unit_cost' => 150.0000,
        'reference_type' => 'TEST',
        'reference_id' => random_int(1000, 999999),
        'user_id' => $this->user->id,
        'occurred_at' => now(),
    ]);

    if ($shouldPass) {
        $movement = $action();
        expect($movement->id)->toBeInt();
    } else {
        expect($action)->toThrow(QueryException::class);
    }
})->with([
    'SALE dengan delta negatif (lulus)' => ['SALE', -5.0, true],
    'SALE dengan delta positif (gagal)' => ['SALE', 5.0, false],
    'WASTE dengan delta negatif (lulus)' => ['WASTE', -2.0, true],
    'WASTE dengan delta positif (gagal)' => ['WASTE', 2.0, false],
    'OPENING dengan delta positif (lulus)' => ['OPENING', 10.0, true],
    'OPENING dengan delta negatif (gagal)' => ['OPENING', -10.0, false],
    'PURCHASE dengan delta positif (lulus)' => ['PURCHASE', 25.0, true],
    'PURCHASE dengan delta negatif (gagal)' => ['PURCHASE', -25.0, false],
    'RETURN dengan delta positif (lulus)' => ['RETURN', 1.0, true],
    'RETURN dengan delta negatif (gagal)' => ['RETURN', -1.0, false],
    'ADJUSTMENT dengan delta positif (lulus)' => ['ADJUSTMENT', 3.0, true],
    'ADJUSTMENT dengan delta negatif (lulus)' => ['ADJUSTMENT', -3.0, true],
    'delta 0 dilarang (gagal)' => ['ADJUSTMENT', 0.0, false],
]);

it('UPDATE stock_movements ditolak oleh trigger DB append-only (AC2, ADR-007, BR-020)', function () {
    $movement = StockMovement::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'type' => 'SALE',
        'quantity_delta' => -10.0000,
        'quantity_before' => 100.0000,
        'quantity_after' => 90.0000,
        'unit_cost' => 150.0000,
        'reference_type' => 'ORDER',
        'reference_id' => 99,
        'user_id' => $this->user->id,
        'occurred_at' => now(),
    ]);

    expect(fn () => DB::transaction(fn () => DB::statement('UPDATE stock_movements SET note = ? WHERE id = ?', ['fraud', $movement->id])))
        ->toThrow(QueryException::class, 'append-only');

    expect(StockMovement::find($movement->id)?->note)->toBeNull();
});

it('DELETE stock_movements ditolak oleh trigger DB append-only (AC2, ADR-007, BR-020)', function () {
    $movement = StockMovement::create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->ingredient->id,
        'type' => 'SALE',
        'quantity_delta' => -10.0000,
        'quantity_before' => 100.0000,
        'quantity_after' => 90.0000,
        'unit_cost' => 150.0000,
        'reference_type' => 'ORDER',
        'reference_id' => 100,
        'user_id' => $this->user->id,
        'occurred_at' => now(),
    ]);

    expect(fn () => DB::transaction(fn () => DB::statement('DELETE FROM stock_movements WHERE id = ?', [$movement->id])))
        ->toThrow(QueryException::class, 'append-only');

    expect(StockMovement::where('id', $movement->id)->exists())->toBeTrue();
});

it('stock_movements tidak punya kolom updated_at (append-only)', function () {
    expect(Schema::hasColumn('stock_movements', 'updated_at'))->toBeFalse();
});
