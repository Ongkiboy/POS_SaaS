<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\PermissionMatrix;

const ME_URL = '/api/v1/me';

function meOutlet(Business $business, string $code): Outlet
{
    return Outlet::factory()->for($business)->create(['code' => $code]);
}

it('tanpa login → 401 UNAUTHENTICATED', function () {
    $this->getJson(ME_URL)
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('TC-AUTH-007: Cashier → role, hanya outlet yang di-assign, permission + scope', function () {
    $business = rbacSeededBusiness();
    $kp01 = meOutlet($business, 'KP01');
    meOutlet($business, 'KP02');
    $cashier = rbacUserWithRole($business, 'CASHIER', [$kp01]);

    $response = $this->actingAs($cashier, 'web')->getJson(ME_URL)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.id', $cashier->id)
        ->assertJsonPath('data.user.name', $cashier->name)
        ->assertJsonPath('data.user.email', $cashier->email)
        ->assertJsonPath('data.user.role', 'CASHIER')
        ->assertJsonCount(1, 'data.outlets')
        ->assertJsonPath('data.outlets.0.id', $kp01->id)
        ->assertJsonPath('data.outlets.0.code', 'KP01')
        ->assertJsonPath('data.outlets.0.timezone', 'Asia/Jakarta');

    $scopes = collect($response->json('data.permissions'))->pluck('scope', 'code');

    expect($scopes->get('sales.view'))->toBe('OWN_ONLY')
        ->and($scopes->get('sales.cancel'))->toBe('LIMITED')
        ->and($scopes->has('sales.create'))->toBeTrue()
        ->and($scopes->get('sales.create'))->toBeNull()
        ->and($scopes->has('users.create'))->toBeFalse();
});

it('AC2: Owner otomatis mendapat semua outlet business-nya, bukan outlet business lain', function () {
    $business = rbacSeededBusiness();
    meOutlet($business, 'KP02');
    meOutlet($business, 'KP01');
    meOutlet(Business::factory()->create(), 'LAIN');
    $owner = rbacUserWithRole($business, 'OWNER'); // tanpa baris user_outlets

    $this->actingAs($owner, 'web')->getJson(ME_URL)
        ->assertOk()
        ->assertJsonPath('data.user.role', 'OWNER')
        ->assertJsonCount(2, 'data.outlets')
        ->assertJsonPath('data.outlets.0.code', 'KP01')
        ->assertJsonPath('data.outlets.1.code', 'KP02');
});

it('outlet yang sudah di-soft delete tidak ikut', function () {
    $business = rbacSeededBusiness();
    meOutlet($business, 'KP01');
    meOutlet($business, 'KP02')->delete();
    $owner = rbacUserWithRole($business, 'OWNER');

    $this->actingAs($owner, 'web')->getJson(ME_URL)
        ->assertOk()
        ->assertJsonCount(1, 'data.outlets');
});

it('daftar permission tiap role sama dengan matriks SRS §2.1', function (string $role) {
    $business = rbacSeededBusiness();
    $user = rbacUserWithRole($business, $role);

    $codes = collect($this->actingAs($user, 'web')->getJson(ME_URL)->assertOk()->json('data.permissions'))
        ->pluck('code')->sort()->values()->all();

    $expected = PermissionMatrix::rolePermissions()[$role];
    sort($expected);

    expect($codes)->toBe($expected);
})->with(PermissionMatrix::ROLES);

it('tidak membocorkan password hash, remember_token, atau status lockout', function () {
    $business = rbacSeededBusiness();
    $owner = rbacUserWithRole($business, 'OWNER');

    $this->actingAs($owner, 'web')->getJson(ME_URL)
        ->assertOk()
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token')
        ->assertJsonMissingPath('data.user.failed_login_count')
        ->assertJsonMissingPath('data.user.locked_until');
});
