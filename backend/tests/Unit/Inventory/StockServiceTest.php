<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Inventory\Stock\Data\StockChange;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Inventory\Stock\Services\StockService;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use App\Support\Exceptions\InsufficientStock;
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
        'sku' => 'BIJI-01',
        'name' => 'Biji Kopi Arabika',
        'base_unit_id' => $this->gram->id,
        'last_cost' => 120.0000,
    ]);

    $this->service = new StockService;
});

it('apply() membuat balance baru jika belum ada dan mencatat movement OPENING', function () {
    $movement = $this->service->apply(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '100.0000',
        referenceType: 'OPENING',
        referenceId: 1,
        userId: $this->user->id,
        note: 'Stok awal',
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->quantity_before)->toBe('0.0000')
        ->and($movement->quantity_delta)->toBe('100.0000')
        ->and($movement->quantity_after)->toBe('100.0000')
        ->and($movement->unit_cost)->toBe('120.0000');

    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))
        ->toBe('100.0000');
});

it('apply() memotong stok normal (SALE) dan memperbarui saldo balance', function () {
    // Siapkan stok awal 50
    $this->service->apply(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '50.0000',
        referenceType: 'OPENING',
        referenceId: 2,
    );

    // Potong 15
    $movement = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-15.0000',
        referenceType: 'ORDER',
        referenceId: 101,
    );

    expect($movement->quantity_before)->toBe('50.0000')
        ->and($movement->quantity_delta)->toBe('-15.0000')
        ->and($movement->quantity_after)->toBe('35.0000');

    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))
        ->toBe('35.0000');
});

it('apply() mengizinkan stok menjadi minus saat allow_negative_stock = true (BR-011)', function () {
    $movement = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 201,
    );

    expect($movement->quantity_after)->toBe('-10.0000');
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('-10.0000');
});

it('apply() melempar InsufficientStock bila stok kurang dan allow_negative_stock = false (AC1, BR-011)', function () {
    $strictOutlet = Outlet::factory()->create([
        'business_id' => $this->business->id,
        'allow_negative_stock' => false,
    ]);

    // Beri stok 5
    $this->service->apply(
        type: 'OPENING',
        outletId: $strictOutlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '5.0000',
        referenceType: 'OPENING',
        referenceId: 3,
    );

    // Coba potong 10 (stok hanya 5)
    expect(fn () => $this->service->apply(
        type: 'SALE',
        outletId: $strictOutlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 301,
    ))->toThrow(function (InsufficientStock $e) {
        expect($e->errorCode)->toBe('INSUFFICIENT_STOCK')
            ->and($e->httpStatus)->toBe(422)
            ->and($e->getMessage())->toContain('Biji Kopi Arabika');
    });

    // Saldo tetap utuh 5
    expect($this->service->getBalance($strictOutlet->id, $this->ingredient->id))->toBe('5.0000');
});

it('apply() memotong stok walau minus jika forceNegative = true (AC2, BR-041)', function () {
    $strictOutlet = Outlet::factory()->create([
        'business_id' => $this->business->id,
        'allow_negative_stock' => false,
    ]);

    // forceNegative = true (settlement gateway)
    $movement = $this->service->apply(
        type: 'SALE',
        outletId: $strictOutlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-20.0000',
        referenceType: 'ORDER',
        referenceId: 401,
        forceNegative: true,
    );

    expect($movement->quantity_after)->toBe('-20.0000');
    expect($this->service->getBalance($strictOutlet->id, $this->ingredient->id))->toBe('-20.0000');
});

it('apply() idempoten: mengembalikan movement lama bila referensi sama (BR-009)', function () {
    $first = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 501,
    );

    $second = $this->service->apply(
        type: 'SALE',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-10.0000',
        referenceType: 'ORDER',
        referenceId: 501,
    );

    expect($second->id)->toBe($first->id);
    // Tidak terpotong dua kali
    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))->toBe('-10.0000');
});

it('applyMany() mengurutkan ingredient_id untuk mencegah deadlock (ADR-002, SYS-01)', function () {
    $sugar = Ingredient::factory()->for($this->business)->create([
        'sku' => 'GULA-01',
        'name' => 'Gula Pasir',
        'base_unit_id' => $this->gram->id,
        'last_cost' => 15.0000,
    ]);

    $changes = [
        new StockChange(
            type: 'OPENING',
            outletId: $this->outlet->id,
            ingredientId: max($this->ingredient->id, $sugar->id),
            quantityDelta: '50.0000',
            referenceType: 'OPENING',
            referenceId: 601,
        ),
        new StockChange(
            type: 'OPENING',
            outletId: $this->outlet->id,
            ingredientId: min($this->ingredient->id, $sugar->id),
            quantityDelta: '100.0000',
            referenceType: 'OPENING',
            referenceId: 602,
        ),
    ];

    $results = $this->service->applyMany($changes);

    expect($results)->toHaveCount(2)
        ->and($this->service->getBalance($this->outlet->id, $this->ingredient->id))->not->toBe('0.0000')
        ->and($this->service->getBalance($this->outlet->id, $sugar->id))->not->toBe('0.0000');
});

it('menjaga presisi desimal 4 digit tanpa distorsi float', function () {
    $this->service->apply(
        type: 'OPENING',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '12.3456',
        referenceType: 'OPENING',
        referenceId: 701,
    );

    $this->service->apply(
        type: 'ADJUSTMENT',
        outletId: $this->outlet->id,
        ingredientId: $this->ingredient->id,
        quantityDelta: '-0.0001',
        referenceType: 'ADJUSTMENT',
        referenceId: 702,
    );

    expect($this->service->getBalance($this->outlet->id, $this->ingredient->id))
        ->toBe('12.3455');
});
