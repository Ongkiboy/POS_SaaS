<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Inventory\Stock\Data\StockChange;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Inventory\Stock\Services\StockService;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = Business::factory()->create();
    $this->outlet = Outlet::factory()->create([
        'business_id' => $this->business->id,
        'allow_negative_stock' => true,
    ]);
    $this->user = User::factory()->create(['business_id' => $this->business->id]);
    $this->gram = Unit::query()->where('code', 'g')->firstOrFail();

    $this->ingredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'MATCHA-01',
        'name' => 'Bubuk Matcha Premium',
        'base_unit_id' => $this->gram->id,
        'last_cost' => 500.0000,
    ]);

    $this->service = new StockService;
});

it('pemanggilan ulang dengan referensi sama mengembalikan movement lama tanpa baris baru (AC1, BR-009, NFR-DATA-003)', function () {
    // Siapkan stok awal
    $this->service->apply(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '200.0000',
        referenceType: 'OPENING',
        referenceId: 1,
    );

    $initialMovementsCount = StockMovement::count();
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('200.0000');

    // Request pemotongan pertama
    $first = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-25.0000',
        referenceType: 'ORDER',
        referenceId: 999,
        userId: $this->user->id,
    );

    expect(StockMovement::count())->toBe($initialMovementsCount + 1)
        ->and($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('175.0000');

    // Retry request kedua dengan referensi sama persis (idempotency retry)
    $second = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-25.0000',
        referenceType: 'ORDER',
        referenceId: 999,
        userId: $this->user->id,
    );

    // Harus mengembalikan objek movement lama yang identik
    expect($second->id)->toBe($first->id)
        ->and($second->quantity_delta)->toBe($first->quantity_delta)
        ->and($second->created_at->toISOString())->toBe($first->created_at->toISOString());

    // Jumlah baris movement TIDAK bertambah
    expect(StockMovement::count())->toBe($initialMovementsCount + 1);

    // Saldo TIDAK terpotong ganda
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('175.0000');
});

it('idempotensi berlaku pada pemanggilan lewat StockChange DTO (AC1)', function () {
    $change = new StockChange(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '50.0000',
        referenceType: 'BATCH',
        referenceId: 888,
    );

    $first = $this->service->apply($change);
    $second = $this->service->apply($change);

    expect($second->id)->toBe($first->id);
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('50.0000');
});

it('referensi berbeda membuat movement baru dan memotong stok sesuai pergerakan (AC1)', function () {
    $m1 = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 1001,
    );

    $m2 = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 1002,
    );

    expect($m2->id)->not->toBe($m1->id);
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('-20.0000');
});

it('reverse() membuat movement lawan dengan referensi baru dan memulihkan saldo (AC2)', function () {
    // Stok awal 100
    $this->service->apply(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '100.0000',
        referenceType: 'OPENING',
        referenceId: 10,
    );

    // Penjualan -30
    $sale = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-30.0000',
        referenceType: 'ORDER',
        referenceId: 555,
    );
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('70.0000');

    // Lakukan pembalikan (mis. restock refund order #555)
    $reversal = $this->service->reverse(
        movement: $sale,
        newReferenceType: 'REFUND',
        newReferenceId: 777,
        note: 'Restock pembatalan order #555',
    );

    expect($reversal->id)->not->toBe($sale->id)
        ->and($reversal->type)->toBe('RETURN')
        ->and($reversal->quantity_delta)->toBe('30.0000')
        ->and($reversal->quantity_before)->toBe('70.0000')
        ->and($reversal->quantity_after)->toBe('100.0000')
        ->and($reversal->reference_type)->toBe('REFUND')
        ->and($reversal->reference_id)->toBe(777);

    // Saldo pulih kembali ke 100
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('100.0000');
});

it('reverse() pemanggilan ulang dengan referensi pembalik yang sama bersifat idempoten (AC1, AC2)', function () {
    $sale = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-15.0000',
        referenceType: 'ORDER',
        referenceId: 666,
    );

    $rev1 = $this->service->reverse($sale, 'REFUND', 888);
    $rev2 = $this->service->reverse($sale, 'REFUND', 888);

    expect($rev2->id)->toBe($rev1->id);
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('0.0000');
});
