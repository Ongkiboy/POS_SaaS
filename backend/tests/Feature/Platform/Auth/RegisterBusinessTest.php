<?php

use App\Modules\Platform\Audit\Models\AuditLog;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\Services\SeedBusinessRoles;
use App\Modules\Platform\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

const REGISTER_URL = '/api/v1/auth/register-business';

function authRegisterPayload(array $override = []): array
{
    return array_merge([
        'business_name' => 'Kopi Pagi',
        'outlet_name' => 'Kopi Pagi Banjar',
        'outlet_code' => 'KP01',
        'timezone' => 'Asia/Jakarta',
        'owner_name' => 'Iki',
        'email' => 'owner@kopipagi.test',
        'password' => 'Rahasia123',
        'password_confirmation' => 'Rahasia123',
    ], $override);
}

it('TC-AUTH-001: data valid → 201 dengan business, outlet, Owner, user_outlets, 5 role, audit', function () {
    $this->postJson(REGISTER_URL, authRegisterPayload())
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Business berhasil dibuat')
        ->assertJsonPath('data.business.name', 'Kopi Pagi')
        ->assertJsonPath('data.outlet.code', 'KP01')
        ->assertJsonPath('data.user.email', 'owner@kopipagi.test')
        ->assertJsonPath('data.user.role', 'OWNER');

    $business = Business::query()->sole();
    $outlet = Outlet::query()->withoutGlobalScopes()->sole();
    $user = User::query()->withoutGlobalScopes()->sole();

    expect($business->status)->toBe('ACTIVE')
        ->and($outlet->business_id)->toBe($business->id)
        ->and($outlet->timezone)->toBe('Asia/Jakarta')
        ->and($user->business_id)->toBe($business->id)
        ->and($user->status)->toBe('ACTIVE')
        ->and(Hash::check('Rahasia123', $user->password))->toBeTrue()
        ->and(DB::table('user_outlets')->where([
            'user_id' => $user->id, 'outlet_id' => $outlet->id, 'business_id' => $business->id,
        ])->exists())->toBeTrue()
        ->and(Role::query()->where('business_id', $business->id)->count())->toBe(5);

    app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
    expect($user->fresh()->hasRole('OWNER'))->toBeTrue();

    $audit = AuditLog::query()->withoutGlobalScopes()->sole();
    expect($audit->action)->toBe('BUSINESS_REGISTERED')
        ->and($audit->business_id)->toBe($business->id)
        ->and($audit->user_id)->toBe($user->id)
        ->and($audit->outlet_id)->toBe($outlet->id)
        ->and(json_encode($audit->after_data))->not->toContain('Rahasia123');
});

it('TC-AUTH-002: email ganda (beda huruf besar/kecil) → 422 pesan SRS, tanpa data baru', function () {
    $this->postJson(REGISTER_URL, authRegisterPayload())->assertCreated();

    $this->postJson(REGISTER_URL, authRegisterPayload([
        'business_name' => 'Kopi Lain', 'outlet_code' => 'KL01', 'email' => 'OWNER@KopiPagi.test',
    ]))
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.email.0', 'Email sudah digunakan, silakan gunakan email lain.');

    expect(Business::query()->count())->toBe(1);
});

it('TC-AUTH-003: password lemah → 422 pesan SRS', function (string $password) {
    $this->postJson(REGISTER_URL, authRegisterPayload(['password' => $password, 'password_confirmation' => $password]))
        ->assertUnprocessable()
        ->assertJsonPath('error.details.password.0', 'Password minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.');

    expect(Business::query()->count())->toBe(0);
})->with([
    'kurang dari 8' => ['Ab1defg'],
    'tanpa huruf besar' => ['rahasia123'],
    'tanpa angka' => ['RahasiaSekali'],
    'tanpa huruf kecil (OI-14)' => ['RAHASIA123'],
]);

it('TC-AUTH-003: konfirmasi password tidak sama → 422 pesan SRS', function () {
    $this->postJson(REGISTER_URL, authRegisterPayload(['password_confirmation' => 'Rahasia124']))
        ->assertUnprocessable()
        ->assertJsonPath('error.details.password.0', 'Konfirmasi password tidak cocok.');
});

it('kode outlet tidak valid → 422 pesan SRS', function (string $code) {
    $this->postJson(REGISTER_URL, authRegisterPayload(['outlet_code' => $code]))
        ->assertUnprocessable()
        ->assertJsonPath('error.details.outlet_code.0', 'Kode outlet 2–6 karakter huruf atau angka.');
})->with(['K', 'KOPI001', 'KP-01', 'KP 1']);

it('timezone tidak valid → 422 pesan SDD', function () {
    $this->postJson(REGISTER_URL, authRegisterPayload(['timezone' => 'Mars/Olympus']))
        ->assertUnprocessable()
        ->assertJsonPath('error.details.timezone.0', 'Timezone tidak valid.');
});

it('field wajib kosong → 422 per field', function () {
    $this->postJson(REGISTER_URL, [])
        ->assertUnprocessable()
        ->assertJsonStructure(['error' => ['details' => [
            'business_name', 'outlet_name', 'outlet_code', 'timezone', 'owner_name', 'email', 'password',
        ]]]);
});

it('satu transaksi: gagal di tengah tidak meninggalkan data apa pun', function () {
    $this->mock(SeedBusinessRoles::class, function ($mock): void {
        $mock->shouldReceive('run')->andThrow(new RuntimeException('gagal seed'));
    });

    $this->postJson(REGISTER_URL, authRegisterPayload())->assertStatus(500);

    expect(Business::query()->count())->toBe(0)
        ->and(Outlet::query()->withoutGlobalScopes()->count())->toBe(0)
        ->and(User::query()->withoutGlobalScopes()->count())->toBe(0)
        ->and(DB::table('user_outlets')->count())->toBe(0)
        ->and(AuditLog::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('slug unik walau nama business sama', function () {
    $this->postJson(REGISTER_URL, authRegisterPayload())->assertCreated();
    $this->postJson(REGISTER_URL, authRegisterPayload(['outlet_code' => 'KP02', 'email' => 'lain@kopipagi.test']))->assertCreated();

    expect(Business::query()->orderBy('id')->pluck('slug')->all())->toBe(['kopi-pagi', 'kopi-pagi-2']);
});
