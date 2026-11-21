<?php

use App\Modules\Platform\Audit\Models\AuditLog;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;

const LOGIN_URL = '/api/v1/auth/login';
const LOGOUT_URL = '/api/v1/auth/logout';
const SPA_ORIGIN = 'http://localhost:5173';

/**
 * Request "stateful" seperti dari SPA (Origin cocok SANCTUM_STATEFUL_DOMAINS di phpunit.xml).
 *
 * @param  array<string, mixed>  $credentials
 */
function authLogin(array $credentials): TestResponse
{
    return test()
        ->withHeaders(['Origin' => SPA_ORIGIN, 'Referer' => SPA_ORIGIN.'/login'])
        ->postJson(LOGIN_URL, $credentials);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function authUser(array $attributes = []): User
{
    return User::factory()
        ->for(Business::factory()->create())
        ->create(array_merge(['email' => 'sari@kopipagi.test'], $attributes));
}

function authAuditCount(string $action, ?string $reason = null): int
{
    return AuditLog::query()
        ->withoutGlobalScopes()
        ->where('action', $action)
        ->when($reason !== null, fn ($q) => $q->where('reason', $reason))
        ->count();
}

// ---------------------------------------------------------------- TC-AUTH-004

it('TC-AUTH-004: login sukses → 200, sesi aktif, hitungan di-reset, last_login_at terisi, audit LOGIN', function () {
    $business = rbacSeededBusiness();
    $user = rbacUserWithRole($business, 'OWNER');
    $user->forceFill(['failed_login_count' => 3])->save();

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login berhasil')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.name', $user->name)
        ->assertJsonPath('data.user.role', 'OWNER')
        ->assertJsonMissingPath('data.user.email')
        ->assertJsonMissingPath('data.user.password');

    $this->assertAuthenticatedAs($user, 'web');

    $user->refresh();
    expect($user->failed_login_count)->toBe(0)
        ->and($user->locked_until)->toBeNull()
        ->and($user->last_login_at)->not->toBeNull();

    $log = AuditLog::query()->withoutGlobalScopes()->where('action', 'LOGIN')->sole();
    expect($log->user_id)->toBe($user->id)
        ->and($log->business_id)->toBe($business->id)
        ->and($log->resource_type)->toBe('users')
        ->and($log->resource_id)->toBe($user->id)
        ->and($log->ip_address)->toBe('127.0.0.1')
        ->and($log->before_data)->toBeNull()
        ->and($log->after_data)->toBeNull();

    expect(json_encode(AuditLog::query()->withoutGlobalScopes()->get()))->not->toContain('Rahasia123');
});

it('TC-AUTH-010: login tanpa Origin/Referer (non-stateful) → 403, tanpa audit LOGIN dan last_login_at', function () {
    $user = authUser();

    test()->postJson(LOGIN_URL, ['email' => $user->email, 'password' => 'Rahasia123'])
        ->assertForbidden();

    expect(authAuditCount('LOGIN'))->toBe(0)
        ->and($user->refresh()->last_login_at)->toBeNull();
});

it('TC-AUTH-004: pesan gagal sama untuk email tak dikenal dan password salah, tidak membocorkan input', function () {
    $user = authUser();

    $wrongPassword = authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS')
        ->assertJsonPath('error.message', 'Email atau password salah.');

    $unknown = authLogin(['email' => 'tidak.ada@kopipagi.test', 'password' => 'SalahBanget9'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS')
        ->assertJsonPath('error.message', 'Email atau password salah.');

    expect($wrongPassword->json('error'))->toBe($unknown->json('error'))
        ->and($wrongPassword->getContent())->not->toContain($user->email)->not->toContain('SalahBanget9')
        ->and($unknown->getContent())->not->toContain('tidak.ada@kopipagi.test')->not->toContain('SalahBanget9');

    $this->assertGuest('web');
});

it('TC-AUTH-004: password salah untuk user dikenal → audit LOGIN_FAILED dengan user_id, tanpa password', function () {
    $user = authUser();

    authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])->assertStatus(422);

    $log = AuditLog::query()->withoutGlobalScopes()->where('action', 'LOGIN_FAILED')->sole();
    expect($log->user_id)->toBe($user->id)
        ->and($log->business_id)->toBe($user->business_id)
        ->and($log->reason)->toBe('INVALID_CREDENTIALS')
        ->and($log->after_data)->toEqual(['failed_login_count' => 1, 'locked' => false]) // toEqual: JSONB tidak menjaga urutan key
        ->and(json_encode($log->toArray()))->not->toContain('SalahBanget9');
});

it('email dicocokkan tanpa memperhatikan huruf besar/kecil', function () {
    authUser(['email' => 'Sari@KopiPagi.Test']);

    authLogin(['email' => 'sari@kopipagi.test', 'password' => 'Rahasia123'])->assertOk();
});

it('user yang di-soft delete tidak bisa login', function () {
    $user = authUser();
    $user->delete();

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

    $this->assertGuest('web');
});

it('validasi: email/password kosong dan format email salah → 422 VALIDATION_ERROR', function () {
    authLogin([])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.email.0', 'Email wajib diisi.')
        ->assertJsonPath('error.details.password.0', 'Password wajib diisi.');

    authLogin(['email' => 'bukan-email', 'password' => 'x'])
        ->assertStatus(422)
        ->assertJsonPath('error.details.email.0', 'Format email tidak valid.');
});

// ---------------------------------------------------------------- TC-AUTH-005

it('TC-AUTH-005: gagal ke-5 → 423 ACCOUNT_LOCKED dan locked_until = sekarang + 15 menit; gagal 1–4 → 422', function () {
    $this->freezeTime();
    $user = authUser();

    foreach ([1, 2, 3, 4] as $attempt) {
        authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

        expect($user->refresh()->failed_login_count)->toBe($attempt)
            ->and($user->locked_until)->toBeNull();
    }

    authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])
        ->assertStatus(423)
        ->assertJsonPath('error.code', 'ACCOUNT_LOCKED')
        ->assertJsonPath('error.message', 'Akun terkunci sementara. Coba lagi dalam 15 menit.');

    $user->refresh();
    expect($user->failed_login_count)->toBe(5)
        ->and(abs($user->locked_until->getTimestamp() - now()->addMinutes(15)->getTimestamp()))->toBeLessThanOrEqual(1);

    $this->assertGuest('web');
});

it('TC-AUTH-005: saat terkunci, password benar tetap 423 dan kunci tidak diperpanjang', function () {
    $this->freezeTime();
    $lockedUntil = now()->addMinutes(10);
    $user = authUser(['failed_login_count' => 5, 'locked_until' => $lockedUntil]);

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])
        ->assertStatus(423)
        ->assertJsonPath('error.code', 'ACCOUNT_LOCKED');

    authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])->assertStatus(423);

    $user->refresh();
    expect($user->failed_login_count)->toBe(5)
        ->and($user->locked_until->getTimestamp())->toBe($lockedUntil->getTimestamp())
        ->and(authAuditCount('LOGIN_FAILED', 'ACCOUNT_LOCKED'))->toBe(2);

    $this->assertGuest('web');
});

it('TC-AUTH-005: setelah 15 menit kunci kedaluwarsa → login sukses dan hitungan di-reset', function () {
    $this->freezeTime();
    $user = authUser(['failed_login_count' => 5, 'locked_until' => now()->addMinutes(15)]);

    $this->travelTo(now()->addMinutes(16));

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])->assertOk();

    $user->refresh();
    expect($user->failed_login_count)->toBe(0)
        ->and($user->locked_until)->toBeNull();
});

it('TC-AUTH-005: setelah kunci kedaluwarsa, password salah memulai hitungan dari 1 (bukan langsung terkunci lagi)', function () {
    $this->freezeTime();
    $user = authUser(['failed_login_count' => 5, 'locked_until' => now()->addMinutes(15)]);

    $this->travelTo(now()->addMinutes(16));

    authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

    $user->refresh();
    expect($user->failed_login_count)->toBe(1)
        ->and($user->locked_until)->toBeNull();
});

// ---------------------------------------------------------------- TC-AUTH-006

it('TC-AUTH-006: akun INACTIVE dengan password benar → 403, tanpa sesi, hitungan tidak berubah, audit ACCOUNT_INACTIVE', function () {
    $user = authUser(['status' => 'INACTIVE']);

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'ACCOUNT_INACTIVE')
        ->assertJsonPath('error.message', 'Akun nonaktif. Hubungi pemilik.');

    $this->assertGuest('web');
    expect($user->refresh()->failed_login_count)->toBe(0)
        ->and(authAuditCount('LOGIN_FAILED', 'ACCOUNT_INACTIVE'))->toBe(1)
        ->and(authAuditCount('LOGIN'))->toBe(0);
});

it('TC-AUTH-006: akun INACTIVE dengan password salah → 422 biasa (status tidak bocor)', function () {
    $user = authUser(['status' => 'INACTIVE']);

    authLogin(['email' => $user->email, 'password' => 'SalahBanget9'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

    expect($user->refresh()->failed_login_count)->toBe(1);
});

it('TC-AUTH-006 (OI-21): email tak dikenal → 422, TIDAK masuk audit_logs, hanya log aplikasi tersamar tanpa password', function () {
    Log::spy();

    authLogin(['email' => 'Hantu@KopiPagi.test', 'password' => 'SalahBanget9'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

    expect(AuditLog::query()->withoutGlobalScopes()->count())->toBe(0);

    Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context): bool {
        $json = (string) json_encode($context);

        return str_contains($message, 'email tidak dikenal')
            && ($context['ip'] ?? null) === '127.0.0.1'
            && str_contains((string) ($context['email'] ?? ''), '***')
            && ! str_contains($json, 'Hantu@KopiPagi.test')
            && ! str_contains($json, 'SalahBanget9');
    });
});

// ---------------------------------------------------------------- throttle (NFR-SEC-006)

it('NFR-SEC-006: percobaan ke-6 dalam semenit untuk email+IP yang sama → 429 RATE_LIMITED; email lain tidak terkena', function () {
    foreach (range(1, 5) as $ignored) {
        authLogin(['email' => 'hantu@kopipagi.test', 'password' => 'SalahBanget9'])->assertStatus(422);
    }

    authLogin(['email' => 'HANTU@kopipagi.test', 'password' => 'SalahBanget9'])
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'RATE_LIMITED')
        ->assertHeader('Retry-After');

    authLogin(['email' => 'lain@kopipagi.test', 'password' => 'SalahBanget9'])->assertStatus(422);
});

// ---------------------------------------------------------------- hardening

it('hash password lama (cost lebih rendah) diganti saat login sukses', function () {
    $user = authUser();
    $oldHash = password_hash('Rahasia123', PASSWORD_BCRYPT, ['cost' => 5]); // phpunit.xml: BCRYPT_ROUNDS=4

    // toBase() melewati cast `hashed` (yang akan menghash ulang nilai ini sebelum disimpan).
    User::query()->withoutGlobalScopes()->whereKey($user->id)->toBase()->update(['password' => $oldHash]);

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])->assertOk();

    $newHash = $user->refresh()->password;
    expect($newHash)->not->toBe($oldHash)
        ->and(password_get_info($newHash)['options']['cost'])->toBe(4)
        ->and(Hash::check('Rahasia123', $newHash))->toBeTrue();
});

it('hash password yang sudah sesuai tidak diubah saat login', function () {
    $user = authUser();
    $hash = $user->password;

    authLogin(['email' => $user->email, 'password' => 'Rahasia123'])->assertOk();

    expect($user->refresh()->password)->toBe($hash);
});

it('respons login tidak memuat field sensitif', function () {
    $user = authUser(['failed_login_count' => 2]);

    $body = authLogin(['email' => $user->email, 'password' => 'Rahasia123'])->assertOk()->getContent();

    foreach (['password', 'remember_token', 'failed_login_count', 'locked_until', 'last_login_at', 'business_id', $user->email] as $secret) {
        expect($body)->not->toContain($secret);
    }
});

// ---------------------------------------------------------------- logout

it('FR-AUTH-002: logout → 204 dan sesi berakhir', function () {
    $user = authUser();

    $this->actingAs($user, 'web')
        ->withHeaders(['Origin' => SPA_ORIGIN, 'Referer' => SPA_ORIGIN.'/'])
        ->postJson(LOGOUT_URL)
        ->assertNoContent();

    $this->assertGuest('web');
});

it('FR-AUTH-002: logout tanpa sesi → 401 UNAUTHENTICATED', function () {
    $this->withHeaders(['Origin' => SPA_ORIGIN, 'Referer' => SPA_ORIGIN.'/'])
        ->postJson(LOGOUT_URL)
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
