<?php

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Inventory\Stock\Models\StockBalance;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Inventory\Stock\Models\StockOpeningBatch;
use App\Modules\Platform\Audit\Models\AuditLog;
use App\Modules\Platform\Outlet\Models\Outlet;
use Database\Seeders\UnitSeeder;

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = rbacSeededBusiness();
    $this->outlet = Outlet::factory()->for($this->business)->create();

    $this->owner = rbacUserWithRole($this->business, 'OWNER');
    $this->manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet]);
    $this->inventory = rbacUserWithRole($this->business, 'INVENTORY', [$this->outlet]);
    $this->cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet]);
    $this->kitchen = rbacUserWithRole($this->business, 'KITCHEN', [$this->outlet]);

    $this->gram = Unit::query()->where('code', 'g')->firstOrFail();
    $this->ml = Unit::query()->where('code', 'ml')->firstOrFail();

    $this->matcha = Ingredient::factory()->for($this->business)->create([
        'sku' => 'ING-MATCHA',
        'name' => 'Bubuk Matcha',
        'base_unit_id' => $this->gram->id,
        'last_cost' => '500.0000',
    ]);

    $this->milk = Ingredient::factory()->for($this->business)->create([
        'sku' => 'ING-MILK',
        'name' => 'Susu UHT Segar',
        'base_unit_id' => $this->ml->id,
        'last_cost' => '20.0000',
    ]);
});

it('satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch; audit OPENING_STOCK (AC1, TC-INV-004, FR-INV-001)', function () {
    $payload = [
        'note' => 'Stok awal 12 Okt',
        'items' => [
            [
                'ingredient_id' => $this->matcha->id,
                'quantity' => '20000',
            ],
            [
                'ingredient_id' => $this->milk->id,
                'quantity' => '5000',
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Stok awal berhasil dicatat');

    $batchId = $response->json('data.batch_id');
    expect($batchId)->toBeInt()->toBeGreaterThan(0);

    // Response memuat data items dengan quantity_after yang sesuai
    $items = $response->json('data.items');
    expect($items)->toHaveCount(2)
        ->and($items[0]['ingredient_id'])->toBe($this->matcha->id)
        ->and($items[0]['quantity_after'])->toBe('20000.0000')
        ->and($items[1]['ingredient_id'])->toBe($this->milk->id)
        ->and($items[1]['quantity_after'])->toBe('5000.0000');

    // 1. StockOpeningBatch tercatat di DB
    $batch = StockOpeningBatch::query()->find($batchId);
    expect($batch)->not->toBeNull()
        ->and($batch->outlet_id)->toBe($this->outlet->id)
        ->and($batch->user_id)->toBe($this->inventory->id)
        ->and($batch->note)->toBe('Stok awal 12 Okt');

    // 2. Exactly 2 StockMovement dengan type = OPENING, reference_type = OPENING, reference_id = batch_id
    $movements = StockMovement::query()
        ->where('reference_type', 'OPENING')
        ->where('reference_id', $batchId)
        ->get();

    expect($movements)->toHaveCount(2);

    $matchaMovement = $movements->firstWhere('ingredient_id', $this->matcha->id);
    expect($matchaMovement)->not->toBeNull()
        ->and($matchaMovement->type)->toBe('OPENING')
        ->and($matchaMovement->outlet_id)->toBe($this->outlet->id)
        ->and($matchaMovement->quantity_delta)->toBe('20000.0000')
        ->and($matchaMovement->quantity_before)->toBe('0.0000')
        ->and($matchaMovement->quantity_after)->toBe('20000.0000')
        ->and($matchaMovement->unit_cost)->toBe('500.0000');

    $milkMovement = $movements->firstWhere('ingredient_id', $this->milk->id);
    expect($milkMovement)->not->toBeNull()
        ->and($milkMovement->type)->toBe('OPENING')
        ->and($milkMovement->outlet_id)->toBe($this->outlet->id)
        ->and($milkMovement->quantity_delta)->toBe('5000.0000')
        ->and($milkMovement->quantity_before)->toBe('0.0000')
        ->and($milkMovement->quantity_after)->toBe('5000.0000')
        ->and($milkMovement->unit_cost)->toBe('20.0000');

    // 3. StockBalance terupdate
    $matchaBalance = StockBalance::query()
        ->where('outlet_id', $this->outlet->id)
        ->where('ingredient_id', $this->matcha->id)
        ->first();
    expect($matchaBalance)->not->toBeNull()
        ->and($matchaBalance->quantity)->toBe('20000.0000')
        ->and($matchaBalance->last_movement_id)->toBe($matchaMovement->id);

    $milkBalance = StockBalance::query()
        ->where('outlet_id', $this->outlet->id)
        ->where('ingredient_id', $this->milk->id)
        ->first();
    expect($milkBalance)->not->toBeNull()
        ->and($milkBalance->quantity)->toBe('5000.0000')
        ->and($milkBalance->last_movement_id)->toBe($milkMovement->id);

    // 4. Audit Log OPENING_STOCK tercatat
    $audit = AuditLog::query()
        ->where('action', 'OPENING_STOCK')
        ->where('resource_type', 'stock_opening_batches')
        ->where('resource_id', $batchId)
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->outlet_id)->toBe($this->outlet->id)
        ->and($audit->user_id)->toBe($this->inventory->id)
        ->and($audit->business_id)->toBe($this->business->id)
        ->and($audit->after_data['batch_id'])->toBe($batchId)
        ->and($audit->after_data['note'])->toBe('Stok awal 12 Okt');
});

it('menolak kuantitas <= 0 dengan pesan validasi yang sesuai (AC2, TC-INV-004, FR-INV-001)', function (string $invalidQuantity) {
    $payload = [
        'items' => [
            [
                'ingredient_id' => $this->matcha->id,
                'quantity' => $invalidQuantity,
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('items.0.quantity')
        ->and($errors['items.0.quantity'][0])->toBe('Jumlah harus lebih dari 0.');

    // Tidak ada batch maupun movement yang dibuat
    expect(StockOpeningBatch::count())->toBe(0)
        ->and(StockMovement::count())->toBe(0);
})->with(['0', '-1', '-10.5000', '0.0000']);

it('menolak kuantitas dengan lebih dari 4 desimal (AC2, API-INV-04)', function () {
    $payload = [
        'items' => [
            [
                'ingredient_id' => $this->matcha->id,
                'quantity' => '100.12345',
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('items.0.quantity')
        ->and($errors['items.0.quantity'][0])->toBe('Jumlah maksimal 4 desimal.');
});

it('menolak bahan ganda dalam satu batch (AC2, TC-INV-004, API-INV-04)', function () {
    $payload = [
        'note' => 'Batch dengan bahan ganda',
        'items' => [
            [
                'ingredient_id' => $this->matcha->id,
                'quantity' => '100.0000',
            ],
            [
                'ingredient_id' => $this->matcha->id,
                'quantity' => '50.0000',
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('items.1.ingredient_id')
        ->and($errors['items.1.ingredient_id'][0])->toBe('Bahan ganda dalam satu batch ditolak.');

    expect(StockOpeningBatch::count())->toBe(0)
        ->and(StockMovement::count())->toBe(0);
});

it('menolak ingredient dari business lain (multi-tenant isolation)', function () {
    $otherBusiness = rbacSeededBusiness();
    $foreignIngredient = Ingredient::factory()->for($otherBusiness)->create([
        'sku' => 'FOREIGN-01',
        'name' => 'Bahan Asing',
        'base_unit_id' => $this->gram->id,
    ]);

    $payload = [
        'items' => [
            [
                'ingredient_id' => $foreignIngredient->id,
                'quantity' => '100.0000',
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('items.0.ingredient_id')
        ->and($errors['items.0.ingredient_id'][0])->toBe('Bahan tidak ditemukan.');
});

it('menolak ingredient yang sudah di-soft-delete', function () {
    $deletedIngredient = Ingredient::factory()->for($this->business)->create([
        'sku' => 'DELETED-01',
        'name' => 'Bahan Terhapus',
        'base_unit_id' => $this->gram->id,
    ]);
    $deletedIngredient->delete();

    $payload = [
        'items' => [
            [
                'ingredient_id' => $deletedIngredient->id,
                'quantity' => '100.0000',
            ],
        ],
    ];

    $response = $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertUnprocessable();

    $errors = $response->json('error.details');
    expect($errors)->toHaveKey('items.0.ingredient_id')
        ->and($errors['items.0.ingredient_id'][0])->toBe('Bahan tidak ditemukan.');
});

it('Owner dan Manager dapat mencatat opening stock (RBAC inventory.adjust)', function () {
    // Owner
    $this->actingAs($this->owner)
        ->postJson('/api/v1/inventory/opening-stock', [
            'items' => [
                ['ingredient_id' => $this->matcha->id, 'quantity' => '1000'],
            ],
        ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertCreated();

    // Manager
    $this->actingAs($this->manager)
        ->postJson('/api/v1/inventory/opening-stock', [
            'items' => [
                ['ingredient_id' => $this->milk->id, 'quantity' => '2000'],
            ],
        ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertCreated();
});

it('Cashier dan Kitchen dilarang mencatat opening stock (403 FORBIDDEN)', function () {
    $payload = [
        'items' => [
            ['ingredient_id' => $this->matcha->id, 'quantity' => '1000'],
        ],
    ];

    $this->actingAs($this->cashier)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');

    $this->actingAs($this->kitchen)
        ->postJson('/api/v1/inventory/opening-stock', $payload, [
            'X-Outlet-Id' => (string) $this->outlet->id,
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('request tanpa autentikasi mengembalikan 401 UNAUTHENTICATED', function () {
    $this->postJson('/api/v1/inventory/opening-stock', [
        'items' => [
            ['ingredient_id' => $this->matcha->id, 'quantity' => '1000'],
        ],
    ], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('request tanpa header X-Outlet-Id mengembalikan 403', function () {
    $this->actingAs($this->inventory)
        ->postJson('/api/v1/inventory/opening-stock', [
            'items' => [
                ['ingredient_id' => $this->matcha->id, 'quantity' => '1000'],
            ],
        ])
        ->assertForbidden();
});
