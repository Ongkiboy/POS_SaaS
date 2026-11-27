<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Inventory\Stock\Models\StockAdjustment;
use App\Modules\Inventory\Stock\Models\StockBalance;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Platform\Audit\Models\AuditLog;
use App\Modules\Platform\Outlet\Models\Outlet;
use Database\Seeders\UnitSeeder;

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = rbacSeededBusiness();
    $this->outlet = Outlet::factory()->for($this->business)->create([
        'allow_negative_stock' => false,
    ]);
    $this->outletMinusAllowed = Outlet::factory()->for($this->business)->create([
        'allow_negative_stock' => true,
    ]);

    $this->owner = rbacUserWithRole($this->business, 'OWNER');
    $this->manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet, $this->outletMinusAllowed]);
    $this->inventory = rbacUserWithRole($this->business, 'INVENTORY', [$this->outlet, $this->outletMinusAllowed]);
    $this->cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet]);
    $this->kitchen = rbacUserWithRole($this->business, 'KITCHEN', [$this->outlet]);

    $this->gram = Unit::query()->where('code', 'g')->firstOrFail();

    $this->matcha = Ingredient::factory()->for($this->business)->create([
        'sku' => 'ING-MATCHA',
        'name' => 'Bubuk Matcha',
        'base_unit_id' => $this->gram->id,
        'last_cost' => '500.0000',
    ]);
});

it('POST /inventory/adjustments menambah stok (direction INCREASE) dan menyimpan before/after serta audit (AC1, TC-INV-005, FR-INV-003)', function () {
    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => '2000.0000',
        'reason' => 'Bonus kiriman supplier',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Penyesuaian stok berhasil dicatat')
        ->assertJsonPath('data.ingredient_id', $this->matcha->id)
        ->assertJsonPath('data.quantity_before', '0.0000')
        ->assertJsonPath('data.quantity_delta', '2000.0000')
        ->assertJsonPath('data.quantity_after', '2000.0000')
        ->assertJsonPath('data.reason', 'Bonus kiriman supplier');

    $adjustmentId = $response->json('data.id');
    expect($adjustmentId)->toBeInt()->toBeGreaterThan(0);

    // 1. StockAdjustment record dibuat sebelum movement
    $adjustment = StockAdjustment::query()->find($adjustmentId);
    expect($adjustment)->not->toBeNull()
        ->and($adjustment->outlet_id)->toBe($this->outlet->id)
        ->and($adjustment->ingredient_id)->toBe($this->matcha->id)
        ->and($adjustment->quantity_before)->toBe('0.0000')
        ->and($adjustment->quantity_delta)->toBe('2000.0000')
        ->and($adjustment->quantity_after)->toBe('2000.0000')
        ->and($adjustment->reason)->toBe('Bonus kiriman supplier')
        ->and($adjustment->user_id)->toBe($this->inventory->id);

    // 2. StockMovement dibuat dengan type ADJUSTMENT dan reference_id = adjustment.id
    $movement = StockMovement::query()
        ->where('reference_type', 'ADJUSTMENT')
        ->where('reference_id', $adjustmentId)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('ADJUSTMENT')
        ->and($movement->outlet_id)->toBe($this->outlet->id)
        ->and($movement->ingredient_id)->toBe($this->matcha->id)
        ->and($movement->quantity_delta)->toBe('2000.0000')
        ->and($movement->quantity_before)->toBe('0.0000')
        ->and($movement->quantity_after)->toBe('2000.0000')
        ->and($movement->unit_cost)->toBe('500.0000');

    // 3. Saldo balance terupdate
    $balance = StockBalance::query()
        ->where('outlet_id', $this->outlet->id)
        ->where('ingredient_id', $this->matcha->id)
        ->first();

    expect($balance)->not->toBeNull()
        ->and($balance->quantity)->toBe('2000.0000')
        ->and($balance->last_movement_id)->toBe($movement->id);

    // 4. Audit log STOCK_ADJUSTMENT tersimpan
    $audit = AuditLog::query()
        ->where('action', 'STOCK_ADJUSTMENT')
        ->where('resource_type', 'stock_adjustments')
        ->where('resource_id', $adjustmentId)
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->outlet_id)->toBe($this->outlet->id)
        ->and($audit->user_id)->toBe($this->inventory->id)
        ->and($audit->business_id)->toBe($this->business->id)
        ->and($audit->reason)->toBe('Bonus kiriman supplier');
});

it('POST /inventory/adjustments mengurangi stok (direction DECREASE) dengan delta negatif (AC1, API-INV-05)', function () {
    // Siapkan saldo awal 20000
    StockBalance::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->matcha->id,
        'quantity' => '20000.0000',
    ]);

    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'DECREASE',
        'quantity' => '2000',
        'reason' => 'Selisih stok',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.quantity_before', '20000.0000')
        ->assertJsonPath('data.quantity_delta', '-2000.0000')
        ->assertJsonPath('data.quantity_after', '18000.0000')
        ->assertJsonPath('data.reason', 'Selisih stok');

    $adjustmentId = $response->json('data.id');

    // Movement bertanda negatif
    $movement = StockMovement::query()
        ->where('reference_type', 'ADJUSTMENT')
        ->where('reference_id', $adjustmentId)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->quantity_delta)->toBe('-2000.0000')
        ->and($movement->quantity_after)->toBe('18000.0000');
});

it('menolak pengurangan saat hasil minus dan minus dilarang dengan 422 INSUFFICIENT_STOCK (AC2, TC-INV-005, FR-INV-003, BR-016)', function () {
    // Saldo awal 50, kurangi 100 pada outlet yang melarang minus
    StockBalance::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->matcha->id,
        'quantity' => '50.0000',
    ]);

    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'DECREASE',
        'quantity' => '100.0000',
        'reason' => 'Salah hitung',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK')
        ->assertJsonPath('error.message', 'Stok tidak mencukupi untuk pengurangan ini.');

    // Tidak ada record adjustment, movement, atau audit yang tersimpan
    expect(StockAdjustment::count())->toBe(0)
        ->and(StockMovement::count())->toBe(0)
        ->and(AuditLog::where('action', 'STOCK_ADJUSTMENT')->count())->toBe(0);

    // Saldo tetap 50
    $balance = StockBalance::query()
        ->where('outlet_id', $this->outlet->id)
        ->where('ingredient_id', $this->matcha->id)
        ->first();
    expect($balance->quantity)->toBe('50.0000');
});

it('mengizinkan hasil minus saat allow_negative_stock = true pada outlet (BR-011)', function () {
    // Saldo awal 50, kurangi 100 pada outlet yang mengizinkan minus
    StockBalance::query()->create([
        'outlet_id' => $this->outletMinusAllowed->id,
        'ingredient_id' => $this->matcha->id,
        'quantity' => '50.0000',
    ]);

    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'DECREASE',
        'quantity' => '100.0000',
        'reason' => 'Pengurangan darurat',
    ];

    $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outletMinusAllowed->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.quantity_before', '50.0000')
        ->assertJsonPath('data.quantity_delta', '-100.0000')
        ->assertJsonPath('data.quantity_after', '-50.0000');

    $balance = StockBalance::query()
        ->where('outlet_id', $this->outletMinusAllowed->id)
        ->where('ingredient_id', $this->matcha->id)
        ->first();
    expect($balance->quantity)->toBe('-50.0000');
});

it('menolak request jika alasan kosong (AC1, FR-INV-003, API-INV-05)', function () {
    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => '100.0000',
        'reason' => '',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('reason')
        ->and($errors['reason'][0])->toBe('Alasan wajib diisi.');
});

it('menolak kuantitas <= 0 dengan pesan validasi yang sesuai (AC1, FR-INV-003)', function (string $invalidQuantity) {
    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => $invalidQuantity,
        'reason' => 'Uji kuantitas',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('quantity')
        ->and($errors['quantity'][0])->toBe('Jumlah harus lebih dari 0.');
})->with(['0', '-1', '-50.0000', '0.0000']);

it('menolak kuantitas dengan lebih dari 4 desimal', function () {
    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => '10.12345',
        'reason' => 'Uji desimal',
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('quantity')
        ->and($errors['quantity'][0])->toBe('Jumlah maksimal 4 desimal.');
});

it('GET /inventory/adjustments mengembalikan daftar penyesuaian terpaginasi untuk outlet aktif (API-INV-06)', function () {
    // Buat 3 adjustment di outlet ini
    StockAdjustment::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->matcha->id,
        'quantity_before' => '0.0000',
        'quantity_delta' => '100.0000',
        'quantity_after' => '100.0000',
        'reason' => 'Adj 1',
        'user_id' => $this->inventory->id,
    ]);

    StockAdjustment::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->matcha->id,
        'quantity_before' => '100.0000',
        'quantity_delta' => '-20.0000',
        'quantity_after' => '80.0000',
        'reason' => 'Adj 2',
        'user_id' => $this->inventory->id,
    ]);

    // 1 adjustment di outlet lain (tidak boleh muncul)
    StockAdjustment::query()->create([
        'outlet_id' => $this->outletMinusAllowed->id,
        'ingredient_id' => $this->matcha->id,
        'quantity_before' => '0.0000',
        'quantity_delta' => '50.0000',
        'quantity_after' => '50.0000',
        'reason' => 'Adj Outlet Lain',
        'user_id' => $this->inventory->id,
    ]);

    $response = $this->actingAs($this->inventory)
        ->getJson('/api/v1/inventory/adjustments', [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect($data)->toHaveCount(2)
        ->and($data[0]['reason'])->toBe('Adj 2')
        ->and($data[1]['reason'])->toBe('Adj 1');

    $meta = $response->json('meta');
    expect($meta['total'])->toBe(2);
});

it('GET /inventory/adjustments mendukung filter ingredient_id', function () {
    $milk = Ingredient::factory()->for($this->business)->create([
        'sku' => 'ING-MILK-2',
        'name' => 'Susu Cair',
        'base_unit_id' => $this->gram->id,
    ]);

    StockAdjustment::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $this->matcha->id,
        'quantity_before' => '0.0000',
        'quantity_delta' => '100.0000',
        'quantity_after' => '100.0000',
        'reason' => 'Adj Matcha',
        'user_id' => $this->inventory->id,
    ]);

    StockAdjustment::query()->create([
        'outlet_id' => $this->outlet->id,
        'ingredient_id' => $milk->id,
        'quantity_before' => '0.0000',
        'quantity_delta' => '200.0000',
        'quantity_after' => '200.0000',
        'reason' => 'Adj Milk',
        'user_id' => $this->inventory->id,
    ]);

    $response = $this->actingAs($this->inventory)
        ->getJson("/api/v1/inventory/adjustments?ingredient_id={$milk->id}", [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['ingredient_id'])->toBe($milk->id)
        ->and($data[0]['reason'])->toBe('Adj Milk');
});

it('Owner dan Manager dapat mencatat adjustment (inventory.adjust)', function () {
    // Owner
    $this->actingAs($this->owner)
        ->postJson('/api/v1/inventory/adjustments', [
            'ingredient_id' => $this->matcha->id,
            'direction' => 'INCREASE',
            'quantity' => '500',
            'reason' => 'Penyesuaian Owner',
        ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertCreated();

    // Manager
    $this->actingAs($this->manager)
        ->postJson('/api/v1/inventory/adjustments', [
            'ingredient_id' => $this->matcha->id,
            'direction' => 'INCREASE',
            'quantity' => '300',
            'reason' => 'Penyesuaian Manager',
        ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertCreated();
});

it('Kitchen dan Cashier dilarang mencatat adjustment (403 FORBIDDEN)', function () {
    $payload = [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => '500',
        'reason' => 'Coba adjust',
    ];

    $this->actingAs($this->cashier)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');

    $this->actingAs($this->kitchen)
        ->postJson('/api/v1/inventory/adjustments', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('Kitchen dapat melihat daftar adjustment (inventory.view)', function () {
    $this->actingAs($this->kitchen)
        ->getJson('/api/v1/inventory/adjustments', [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertOk();
});

it('request tanpa autentikasi mengembalikan 401 UNAUTHENTICATED', function () {
    $this->postJson('/api/v1/inventory/adjustments', [
        'ingredient_id' => $this->matcha->id,
        'direction' => 'INCREASE',
        'quantity' => '500',
        'reason' => 'Uji auth',
    ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
