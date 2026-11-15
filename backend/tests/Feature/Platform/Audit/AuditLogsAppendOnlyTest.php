<?php

use App\Modules\Platform\Business\Models\Business;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->logId = DB::table('audit_logs')->insertGetId([
        'business_id' => $this->business->id,
        'action' => 'BUSINESS_REGISTERED',
        'resource_type' => 'businesses',
        'resource_id' => $this->business->id,
        'after_data' => json_encode(['name' => 'Kopi Pagi']),
    ]);
});

it('INSERT diizinkan dan created_at terisi', function () {
    $row = DB::table('audit_logs')->find($this->logId);

    expect($row->created_at)->not->toBeNull()
        ->and(json_decode($row->after_data, true))->toBe(['name' => 'Kopi Pagi']);
});

it('UPDATE ditolak trigger DB dan data tidak berubah (AC1)', function () {
    expect(fn () => DB::transaction(fn () => DB::table('audit_logs')->where('id', $this->logId)->update(['action' => 'X'])))
        ->toThrow(QueryException::class, 'append-only');

    expect(DB::table('audit_logs')->find($this->logId)->action)->toBe('BUSINESS_REGISTERED');
});

it('DELETE ditolak trigger DB dan baris tetap ada (AC1)', function () {
    expect(fn () => DB::transaction(fn () => DB::table('audit_logs')->where('id', $this->logId)->delete()))
        ->toThrow(QueryException::class, 'append-only');

    expect(DB::table('audit_logs')->where('id', $this->logId)->exists())->toBeTrue();
});

it('index sesuai ERD §4.1 (AC2)', function () {
    $indexes = collect(DB::select("select indexname, indexdef from pg_indexes where tablename = 'audit_logs'"))
        ->pluck('indexdef', 'indexname');

    expect($indexes)->toHaveKey('audit_logs_business_created_at_index')
        ->and($indexes['audit_logs_business_created_at_index'])->toContain('(business_id, created_at DESC)')
        ->and($indexes['audit_logs_business_action_created_at_index'])->toContain('(business_id, action, created_at DESC)')
        ->and($indexes['audit_logs_resource_index'])->toContain('(resource_type, resource_id)')
        ->and($indexes['audit_logs_user_created_at_index'])->toContain('(user_id, created_at DESC)');
});

it('tidak punya kolom updated_at (append-only)', function () {
    expect(Schema::hasColumn('audit_logs', 'updated_at'))->toBeFalse();
});
