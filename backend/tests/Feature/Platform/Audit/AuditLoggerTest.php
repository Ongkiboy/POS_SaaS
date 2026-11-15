<?php

use App\Modules\Platform\Audit\Models\AuditLog;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\OutletContext;
use App\Support\Http\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->business = rbacSeededBusiness();
    $this->outlet = Outlet::factory()->for($this->business)->create();
    $this->manager = rbacUserWithRole($this->business, 'MANAGER', [$this->outlet]);
});

it('di dalam transaksi baru tertulis setelah commit (AC1)', function () {
    $this->actingAs($this->manager);

    DB::transaction(function () {
        AuditLogger::log('SETTING_CHANGED', $this->outlet, ['tax_rate' => '10.00'], ['tax_rate' => '11.00']);

        expect(AuditLog::query()->count())->toBe(0); // belum commit
    });

    expect(AuditLog::query()->count())->toBe(1);
});

it('transaksi rollback tidak menulis audit', function () {
    $this->actingAs($this->manager);

    try {
        DB::transaction(function () {
            AuditLogger::log('SETTING_CHANGED', $this->outlet, null, ['x' => 1]);
            throw new RuntimeException('batal');
        });
    } catch (RuntimeException) {
        // diharapkan
    }

    expect(AuditLog::query()->count())->toBe(0);
});

it('payload memuat before/after JSON, user, outlet, IP, resource (AC2)', function () {
    Route::middleware(['api', 'auth', 'outlet.context'])->post('/api/v1/__uji/audit', function () {
        DB::transaction(fn () => AuditLogger::log(
            'SETTING_CHANGED', app(OutletContext::class)->outlet(), ['tax_rate' => '10.00'], ['tax_rate' => '11.00'], 'uji',
        ));

        return ApiResponse::success();
    });

    $this->actingAs($this->manager)
        ->postJson('/api/v1/__uji/audit', [], ['X-Outlet-Id' => (string) $this->outlet->id])
        ->assertOk();

    $log = AuditLog::query()->sole();

    expect($log->business_id)->toBe($this->business->id)
        ->and($log->user_id)->toBe($this->manager->id)
        ->and($log->outlet_id)->toBe($this->outlet->id)
        ->and($log->ip_address)->toBe('127.0.0.1')
        ->and($log->resource_type)->toBe('outlets')
        ->and($log->resource_id)->toBe($this->outlet->id)
        ->and($log->before_data)->toBe(['tax_rate' => '10.00'])
        ->and($log->after_data)->toBe(['tax_rate' => '11.00'])
        ->and($log->reason)->toBe('uji')
        ->and($log->created_at)->not->toBeNull();
});

it('konteks eksplisit untuk alur tanpa login (register)', function () {
    AuditLogger::log('BUSINESS_REGISTERED', $this->business, null, ['name' => 'X'],
        userId: $this->manager->id, outletId: $this->outlet->id, businessId: $this->business->id);

    $log = AuditLog::query()->withoutGlobalScopes()->sole();

    expect($log->user_id)->toBe($this->manager->id)
        ->and($log->resource_type)->toBe('businesses');
});

it('tanpa business_id sama sekali ditolak', function () {
    expect(fn () => AuditLogger::log('LOGIN_FAILED', ['users', null]))->toThrow(LogicException::class);
});

it('model AuditLog menolak update dan delete di level aplikasi', function () {
    $this->actingAs($this->manager);
    AuditLogger::log('SETTING_CHANGED', $this->outlet, null, ['x' => 1]);
    $log = AuditLog::query()->sole();

    expect(fn () => $log->update(['action' => 'X']))->toThrow(LogicException::class)
        ->and(fn () => $log->delete())->toThrow(LogicException::class);
});
