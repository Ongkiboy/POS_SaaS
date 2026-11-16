<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Rbac\Services\PermissionScopeResolver;
use App\Modules\Platform\Rbac\Services\SeedBusinessRoles;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Disalin dari SRS v3.0 §2.1 (bukan dari PermissionMatrix) supaya test benar-benar membandingkan.
dataset('matriks SRS', [
    'CASHIER' => ['CASHIER', [
        'sales.view', 'sales.create', 'sales.update', 'sales.cancel', 'sales.pay', 'discounts.apply',
        'shifts.open', 'shifts.close', 'cash.manage', 'products.view', 'reports.view', 'dashboard.view', 'outlets.view',
    ]],
    'INVENTORY' => ['INVENTORY', [
        'products.view', 'recipes.view', 'inventory.view', 'inventory.adjust', 'inventory.count', 'inventory.waste',
        'reports.view', 'dashboard.view', 'outlets.view', 'purchases.view', 'purchases.create', 'purchases.receive',
        'purchases.cancel', 'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
    ]],
    'KITCHEN' => ['KITCHEN', [
        'orders.kitchen_view', 'orders.kitchen_update', 'products.view', 'recipes.view',
        'inventory.view', 'inventory.waste', 'outlets.view',
    ]],
]);

function dbRbacUser(Business $business, string $role): User
{
    $user = User::factory()->for($business)->create();
    app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
    $user->assignRole($role);

    return $user->fresh();
}

beforeEach(function () {

    $this->business = Business::factory()->create();
    app(SeedBusinessRoles::class)->run($this->business);
});

it('membuat 5 role untuk business dan 44 permission global', function () {
    $roles = Role::query()->where('business_id', $this->business->id)->pluck('name')->all();

    expect($roles)->toEqualCanonicalizing(['OWNER', 'MANAGER', 'CASHIER', 'INVENTORY', 'KITCHEN'])
        ->and(Permission::query()->count())->toBe(44);
});

it('role terbatas mendapat permission persis sesuai SRS §2.1', function (string $roleName, array $expected) {
    $role = Role::query()->where('business_id', $this->business->id)->where('name', $roleName)->firstOrFail();

    expect($role->permissions->pluck('name')->all())->toEqualCanonicalizing($expected);
})->with('matriks SRS');

it('OWNER mendapat semua dan MANAGER semua kecuali payment_config.manage dan outlets.manage', function () {
    $owner = Role::query()->where('business_id', $this->business->id)->where('name', 'OWNER')->firstOrFail();
    $manager = Role::query()->where('business_id', $this->business->id)->where('name', 'MANAGER')->firstOrFail();

    expect($owner->permissions)->toHaveCount(44)
        ->and($manager->permissions)->toHaveCount(42)
        ->and($manager->permissions->pluck('name')->all())->not->toContain('payment_config.manage')
        ->and($manager->permissions->pluck('name')->all())->not->toContain('outlets.manage');
});

it('seed idempoten dan role tiap business terpisah', function () {
    app(SeedBusinessRoles::class)->run($this->business);
    $other = Business::factory()->create();
    app(SeedBusinessRoles::class)->run($other);

    expect(Role::query()->count())->toBe(10)
        ->and(Role::query()->where('business_id', $this->business->id)->count())->toBe(5)
        ->and(Permission::query()->count())->toBe(44);
});

it('role milik business lain tidak berlaku di team berbeda', function () {
    $user = dbRbacUser($this->business, 'OWNER');
    $other = Business::factory()->create();
    app(SeedBusinessRoles::class)->run($other);

    app(PermissionRegistrar::class)->setPermissionsTeamId($other->id);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    expect($user->hasRole('OWNER'))->toBeFalse();
});

it('scope CASHIER terbaca untuk /me (AC2)', function () {
    $permissions = collect(app(PermissionScopeResolver::class)->forUser(dbRbacUser($this->business, 'CASHIER')))
        ->pluck('scope', 'code');

    expect($permissions)->toHaveCount(13)
        ->and($permissions['sales.view'])->toBe('OWN_ONLY')
        ->and($permissions['sales.cancel'])->toBe('LIMITED')
        ->and($permissions['reports.view'])->toBe('LIMITED')
        ->and($permissions['dashboard.view'])->toBe('LIMITED')
        ->and($permissions['sales.create'])->toBeNull();
});

it('scope MANAGER LIMITED untuk users, settings, audit; OWNER tanpa scope', function () {
    $resolver = app(PermissionScopeResolver::class);
    $manager = dbRbacUser($this->business, 'MANAGER');
    $owner = dbRbacUser($this->business, 'OWNER');

    expect($resolver->scopeFor($manager, 'users.create'))->toBe('LIMITED')
        ->and($resolver->scopeFor($manager, 'audit.view'))->toBe('LIMITED')
        ->and($resolver->scopeFor($manager, 'sales.view'))->toBeNull()
        ->and(collect($resolver->forUser($owner))->whereNotNull('scope'))->toBeEmpty();
});

it('CHECK nama role menolak role di luar lima role ERD', function () {
    expect(fn () => DB::transaction(fn () => DB::table('roles')->insert([
        'business_id' => $this->business->id, 'name' => 'ADMIN', 'guard_name' => 'web',
        'created_at' => now(), 'updated_at' => now(),
    ])))->toThrow(QueryException::class);
});
