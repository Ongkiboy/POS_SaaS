<?php

namespace App\Modules\Platform\Audit\Services;

use App\Modules\Platform\Audit\Jobs\WriteAuditLogJob;
use App\Modules\Platform\Tenant\OutletContext;
use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use LogicException;

/**
 * Pencatatan audit FR-AUD-001. Ditulis setelah commit lewat queue.
 */
final class AuditLogger
{
    /**
     * @param  Model|array{0: string, 1: int|null}  $resource
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function log(
        string $action,
        Model|array $resource,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        ?int $userId = null,
        ?int $outletId = null,
        ?int $businessId = null,
    ): void {
        [$resourceType, $resourceId] = self::resource($resource);

        $businessId ??= app(TenantContext::class)->id()
            ?? ($resource instanceof Model ? self::intOrNull($resource->getAttribute('business_id')) : null);

        if ($businessId === null) {
            throw new LogicException("Audit {$action} tanpa business_id.");
        }

        $userId ??= self::intOrNull(Auth::id());
        $outletId ??= app(OutletContext::class)->id();

        WriteAuditLogJob::dispatch([
            'business_id' => $businessId,
            'outlet_id' => $outletId,
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'before_data' => $before,
            'after_data' => $after,
            'reason' => $reason,
            'ip_address' => request()->ip(),
        ])->afterCommit();
    }

    /**
     * Validasi runtime dipertahankan: pemanggil boleh datang dari kode tanpa analisis statis.
     *
     * @param  Model|array<int, mixed>  $resource
     * @return array{0: string, 1: int|null}
     */
    private static function resource(Model|array $resource): array
    {
        if ($resource instanceof Model) {
            return [$resource->getTable(), self::intOrNull($resource->getKey())];
        }

        if (! isset($resource[0]) || ! is_string($resource[0]) || $resource[0] === '') {
            throw new InvalidArgumentException('Resource audit harus Model atau [type, id].');
        }

        return [$resource[0], $resource[1] ?? null];
    }

    private static function intOrNull(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }
}
