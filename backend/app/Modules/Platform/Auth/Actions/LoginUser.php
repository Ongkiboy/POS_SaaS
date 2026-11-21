<?php

namespace App\Modules\Platform\Auth\Actions;

use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Auth\Data\LoginInput;
use App\Modules\Platform\Auth\Exceptions\AccountInactiveException;
use App\Modules\Platform\Auth\Exceptions\AccountLockedException;
use App\Modules\Platform\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Platform\User\Models\User;
use App\Support\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * FR-AUTH-002 / BR-023. Memeriksa kredensial dan mengelola hitungan gagal + kunci akun.
 * Tidak menyentuh sesi (itu urusan controller).
 */
final class LoginUser
{
    /**
     * Maksimal percobaan login gagal sebelum akun dikunci.
     */
    public const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Jumlah menit akun dikunci setelah melebihi percobaan login gagal.
     */
    public const LOCK_MINUTES = 15;

    /**
     * Hash dummy untuk menyamakan waktu respons saat email tidak dikenal.
     */
    private static ?string $dummyHash = null;

    /**
     * @throws ApiException
     */
    public function handle(LoginInput $input): User
    {
        // Perubahan hitungan harus ter-commit walau hasilnya error, jadi exception
        // dilempar SETELAH transaksi selesai (di dalam transaksi akan ikut rollback).
        $result = DB::transaction(fn (): User|ApiException => $this->attempt($input));

        if ($result instanceof ApiException) {
            throw $result;
        }

        return $result;
    }

    private function attempt(LoginInput $input): User|ApiException
    {
        // Belum ada konteks tenant saat login. whereNull('deleted_at') wajib karena
        // withoutGlobalScopes() juga mematikan SoftDeletes.
        $user = User::query()
            ->withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereRaw('lower(email) = ?', [mb_strtolower($input->email)])
            ->lockForUpdate()
            ->first();

        if ($user === null) {
            // Waktu respons disamakan dengan kasus "email ada, password salah".
            Hash::check($input->password, self::dummyHash());

            // OI-21: tidak masuk audit_logs (business_id NOT NULL). Tanpa password, email tersamar.
            Log::warning('Login gagal: email tidak dikenal', [
                'ip' => request()->ip(),
                'email' => self::maskEmail($input->email),
            ]);

            return new InvalidCredentialsException;
        }

        // Terkunci: tidak menambah hitungan, tidak memperpanjang kunci.
        if ($user->locked_until !== null && $user->locked_until->isFuture()) {
            $this->auditFailed($user, 'ACCOUNT_LOCKED');

            return new AccountLockedException;
        }

        // Kunci sudah kedaluwarsa: mulai dari nol (disimpan oleh save() di cabang di bawah).
        if ($user->locked_until !== null) {
            $user->failed_login_count = 0;
            $user->locked_until = null;
        }

        if (! Hash::check($input->password, $user->password)) {
            $user->failed_login_count++;
            $locked = $user->failed_login_count >= self::MAX_FAILED_ATTEMPTS;

            if ($locked) {
                $user->locked_until = now()->addMinutes(self::LOCK_MINUTES);
            }

            $user->save();

            $this->auditFailed($user, 'INVALID_CREDENTIALS', [
                'failed_login_count' => $user->failed_login_count,
                'locked' => $locked,
            ]);

            return $locked ? new AccountLockedException : new InvalidCredentialsException;
        }

        if ($user->status !== 'ACTIVE') {
            $user->save();
            $this->auditFailed($user, 'ACCOUNT_INACTIVE');

            return new AccountInactiveException;
        }

        $user->failed_login_count = 0;
        $user->locked_until = null;
        $user->last_login_at = now();

        // login() tidak merehash otomatis. Cast `hashed` menghash password mentah ini
        // dengan cost konfigurasi saat ini.
        if (Hash::needsRehash($user->password)) {
            $user->password = $input->password;
        }

        $user->save();

        AuditLogger::log('LOGIN', $user, userId: $user->id, businessId: $user->business_id);

        return $user;
    }

    /**
     * @param  array<string, mixed>|null  $after
     */
    private function auditFailed(User $user, string $reason, ?array $after = null): void
    {
        AuditLogger::log(
            'LOGIN_FAILED',
            $user,
            after: $after,
            reason: $reason,
            userId: $user->id,
            businessId: $user->business_id,
        );
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= Hash::make(Str::random(32));
    }

    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 1).'***@'.$domain;
    }
}
