<?php

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\OutletContext;
use App\Support\Http\ApiResponse;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Route::middleware(['api', 'auth', 'outlet.context', 'permission:sales.view'])
        ->get('/api/v1/__uji/rbac', fn () => ApiResponse::success(['outlet_id' => app(OutletContext::class)->id()]));

    $this->business = rbacSeededBusiness();
    $this->outlet1 = Outlet::factory()->for($this->business)->create();
    $this->outlet2 = Outlet::factory()->for($this->business)->create();
});

it('Cashier dengan outlet terdaftar dan permission lolos; OutletContext terisi', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);

    $this->actingAs($cashier)
        ->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $this->outlet1->id])
        ->assertOk()
        ->assertJsonPath('data.outlet_id', $this->outlet1->id);
});

it('tanpa permission mengembalikan 403 FORBIDDEN (AC1)', function () {
    $kitchen = rbacUserWithRole($this->business, 'KITCHEN', [$this->outlet1]);

    $this->actingAs($kitchen)
        ->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $this->outlet1->id])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('X-Outlet-Id di luar user_outlets mengembalikan 403 (AC1)', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);

    $this->actingAs($cashier)
        ->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $this->outlet2->id])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('X-Outlet-Id milik business lain mengembalikan 403 yang identik', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);
    $foreign = Outlet::factory()->for(rbacSeededBusiness())->create();

    $this->actingAs($cashier)
        ->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $foreign->id])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('header X-Outlet-Id hilang atau tidak numerik mengembalikan 403', function (?string $value) {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);
    $headers = $value === null ? [] : ['X-Outlet-Id' => $value];

    $this->actingAs($cashier)->getJson('/api/v1/__uji/rbac', $headers)->assertForbidden();
})->with([null, 'abc', '1 OR 1=1', '-1']);

it('Owner mengakses semua outlet business tanpa baris user_outlets', function () {
    $owner = rbacUserWithRole($this->business, 'OWNER');

    $this->actingAs($owner)
        ->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $this->outlet2->id])
        ->assertOk();
});

it('tanpa login mengembalikan 401', function () {
    $this->getJson('/api/v1/__uji/rbac', ['X-Outlet-Id' => (string) $this->outlet1->id])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('team Spatie otomatis = business user saat login (listener Authenticated)', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    $this->actingAs($cashier);

    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe($this->business->id);
});
