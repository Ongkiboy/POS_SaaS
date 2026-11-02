<?php

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\Services\AccessRules;

beforeEach(function () {
    $this->business = rbacSeededBusiness();
    $this->outlet1 = Outlet::factory()->for($this->business)->create();
    $this->outlet2 = Outlet::factory()->for($this->business)->create();
    $this->rules = app(AccessRules::class);
});

it('Cashier hanya order miliknya untuk sales.view dan sales.cancel (AC2, BR-025)', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);
    $other = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);

    expect($this->rules->canAccessOwned($cashier, 'sales.view', $cashier->id))->toBeTrue()
        ->and($this->rules->canAccessOwned($cashier, 'sales.view', $other->id))->toBeFalse()
        ->and($this->rules->canAccessOwned($cashier, 'sales.cancel', $other->id))->toBeFalse()
        ->and($this->rules->canAccessOwned($cashier, 'sales.view', null))->toBeFalse();
});

it('Manager melihat order siapa pun (tanpa scope); Kitchen tanpa permission ditolak', function () {
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);
    $manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet1]);
    $kitchen = rbacUserWithRole($this->business, 'KITCHEN', [$this->outlet1]);

    expect($this->rules->canAccessOwned($manager, 'sales.view', $cashier->id))->toBeTrue()
        ->and($this->rules->canAccessOwned($kitchen, 'sales.view', $kitchen->id))->toBeFalse();
});

it('Manager hanya mengelola CASHIER, INVENTORY, KITCHEN (AC2, BR-024)', function (string $target, bool $allowed) {
    $manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet1]);

    expect($this->rules->canManageRole($manager, $target))->toBe($allowed);
})->with([
    ['CASHIER', true],
    ['INVENTORY', true],
    ['KITCHEN', true],
    ['MANAGER', false],
    ['OWNER', false],
]);

it('Owner mengelola semua role; Cashier tidak mengelola role apa pun', function () {
    $owner = rbacUserWithRole($this->business, 'OWNER');
    $cashier = rbacUserWithRole($this->business, 'CASHIER', [$this->outlet1]);

    expect($this->rules->canManageRole($owner, 'MANAGER'))->toBeTrue()
        ->and($this->rules->canManageRole($owner, 'OWNER'))->toBeTrue()
        ->and($this->rules->canManageRole($cashier, 'KITCHEN'))->toBeFalse();
});

it('Manager hanya menugaskan outlet yang ia akses', function () {
    $manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet1]);
    $owner = rbacUserWithRole($this->business, 'OWNER');

    expect($this->rules->canAssignOutlets($manager, [$this->outlet1->id]))->toBeTrue()
        ->and($this->rules->canAssignOutlets($manager, [$this->outlet1->id, $this->outlet2->id]))->toBeFalse()
        ->and($this->rules->canAssignOutlets($owner, [$this->outlet2->id]))->toBeTrue();
});
