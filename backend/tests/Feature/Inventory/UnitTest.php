<?php

use Database\Seeders\UnitSeeder;

beforeEach(function () {
    $this->seed(UnitSeeder::class);
    $this->business = rbacSeededBusiness();
    $this->inventoryUser = rbacUserWithRole($this->business, 'INVENTORY');
    $this->cashier = rbacUserWithRole($this->business, 'CASHIER');
});

it('GET /units mengembalikan 5 satuan untuk user dengan permission inventory.view (BE-ING-002 AC1)', function () {
    $response = $this->actingAs($this->inventoryUser)
        ->getJson('/api/v1/units')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(5, 'data');

    /** @var list<string> $codes */
    $codes = collect($response->json('data'))->pluck('code')->all();

    expect($codes)->toEqualCanonicalizing(['g', 'kg', 'ml', 'L', 'pcs']);
});

it('GET /units menolak user tanpa permission inventory.view dengan 403', function () {
    $this->actingAs($this->cashier)
        ->getJson('/api/v1/units')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('GET /units menolak request tanpa login dengan 401', function () {
    $this->getJson('/api/v1/units')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
