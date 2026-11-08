<?php

namespace App\Modules\Platform\Tenant\Http\Middleware;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Tenant\OutletContext;
use App\Modules\Platform\User\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class EnsureOutletContext
{
    public function __construct(
        private readonly OutletContext $outletContext,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        // TD §2.2: team Spatie diset sebelum permission:* dicek.
        $this->registrar->setPermissionsTeamId($user->business_id);

        $header = $request->header('X-Outlet-Id');

        if (! is_string($header) || ! ctype_digit($header)) {
            throw new AccessDeniedHttpException('Outlet aktif belum dipilih.');
        }

        // Model Outlet ber-scope tenant: outlet business lain = null.
        $outlet = Outlet::query()->find((int) $header);

        if ($outlet === null || ! $this->canAccess($user, $outlet)) {
            throw new AccessDeniedHttpException('Outlet tidak termasuk akses Anda.');
        }

        $this->outletContext->set($outlet);

        return $next($request);
    }

    private function canAccess(User $user, Outlet $outlet): bool
    {
        if ($user->hasRole('OWNER')) {
            return true; // FR-AUTH-004: Owner otomatis punya akses semua outlet business.
        }

        return $user->outlets()->whereKey($outlet->getKey())->exists();
    }
}
