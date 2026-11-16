<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\TenantContext;
use App\Modules\Platform\Tenant\TenantQuery;
use App\Modules\Platform\User\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->bizA = Business::factory()->create();
    $this->bizB = Business::factory()->create();
    $this->outletA = Outlet::factory()->for($this->bizA)->create();
    $this->outletB = Outlet::factory()->for($this->bizB)->create();
    $this->userA = User::factory()->for($this->bizA)->create();
    $this->userB = User::factory()->for($this->bizB)->create();
});

it('query model tenant otomatis terfilter business_id user login (AC1)', function () {
    $this->actingAs($this->userA);

    expect(Outlet::query()->pluck('id')->all())->toBe([$this->outletA->id])
        ->and(Outlet::query()->find($this->outletB->id))->toBeNull()
        ->and(User::query()->pluck('id')->all())->toBe([$this->userA->id]);
});

it('insert otomatis mengisi business_id dari konteks (AC2)', function () {
    $this->actingAs($this->userA);

    $outlet = Outlet::query()->create(['code' => 'NEW1', 'name' => 'Baru', 'status' => 'ACTIVE']);

    expect($outlet->business_id)->toBe($this->bizA->id);
});

it('insert tanpa konteks tenant ditolak, tidak diam-diam kosong', function () {
    expect(fn () => Outlet::query()->create(['code' => 'X1', 'name' => 'X', 'status' => 'ACTIVE']))
        ->toThrow(LogicException::class);
});

it('runAs memasang konteks sementara lalu memulihkannya', function () {
    $context = app(TenantContext::class);

    $outlet = $context->runAs($this->bizB->id, fn () => Outlet::query()->create(['code' => 'RB1', 'name' => 'R', 'status' => 'ACTIVE']));

    expect($outlet->business_id)->toBe($this->bizB->id)
        ->and($context->id())->toBeNull();
});

it('data business lain lewat route model binding mengembalikan 404 (AC2)', function () {
    Route::middleware('api')->get('/api/v1/__uji/outlets/{outlet}', fn (Outlet $outlet) => ApiResponse::success(['id' => $outlet->id]));

    $this->actingAs($this->userA);

    $this->getJson('/api/v1/__uji/outlets/'.$this->outletA->id)
        ->assertOk()
        ->assertJsonPath('data.id', $this->outletA->id);

    $this->getJson('/api/v1/__uji/outlets/'.$this->outletB->id)
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('TenantQuery memfilter tabel tenant dan menolak tanpa konteks', function () {
    expect(fn () => TenantQuery::table('outlets')->count())->toThrow(LogicException::class);

    $this->actingAs($this->userA);

    expect(TenantQuery::table('outlets')->pluck('id')->all())->toBe([$this->outletA->id]);
});

it('relasi user ke outlet tetap ter-scope business', function () {
    $this->userA->outlets()->attach($this->outletA->id, ['business_id' => $this->bizA->id]);
    $this->actingAs($this->userA);

    expect($this->userA->outlets()->pluck('outlets.id')->all())->toBe([$this->outletA->id]);
});
