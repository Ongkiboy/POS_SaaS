<?php

namespace App\Modules\Platform\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * Business aktif untuk request/job ini. Didaftarkan sebagai scoped binding.
 */
final class TenantContext
{
    private ?int $businessId = null;

    public function set(?int $businessId): void
    {
        $this->businessId = $businessId;
    }

    public function id(): ?int
    {
        if ($this->businessId !== null) {
            return $this->businessId;
        }

        // hasUser() tidak memicu pengambilan user (hindari rekursi saat Auth memuat User).
        $guard = Auth::guard();

        if (! $guard->hasUser()) {
            return null;
        }

        // user() bertipe kontrak Authenticatable yang TIDAK punya getAttribute().
        // Persempit ke Model, lalu pakai variabel hasil penyempitan ($user), bukan $guard->user() lagi.
        $user = $guard->user();

        if (! $user instanceof Model) {
            return null;
        }

        $businessId = $user->getAttribute('business_id');

        return $businessId === null ? null : (int) $businessId;
    }

    public function requireId(): int
    {
        return $this->id() ?? throw new LogicException('Konteks tenant (business_id) belum diset.');
    }

    /**
     * Jalankan callback dengan business tertentu, lalu pulihkan konteks sebelumnya.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runAs(int $businessId, callable $callback): mixed
    {
        $previous = $this->businessId;
        $this->businessId = $businessId;

        try {
            return $callback();
        } finally {
            $this->businessId = $previous;
        }
    }
}
