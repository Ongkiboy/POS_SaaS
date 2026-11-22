<?php

namespace App\Modules\Platform\Auth\Actions;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\Services\PermissionScopeResolver;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\PermissionRegistrar;

/**
 * FR-AUTH-004: outlet yang boleh diakses dan permission (beserta scope) milik user yang sedang login.
 */
final class GetCurrentUser
{
    public function __construct(
        private readonly PermissionScopeResolver $scopes,
        private readonly PermissionRegistrar $registrar,
    ) {}

    /**
     * @return array{outlets: Collection<int, Outlet>, permissions: list<array{code: string, scope: string|null}>}
     */
    public function handle(User $user): array
    {

        // Role Spatie dibaca per team; jangan bergantung pada urutan listener.
        $this->registrar->setPermissionsTeamId($user->business_id);

        // FR-AUTH-004: Owner otomatis semua outlet business (aturan sama dengan EnsureOutletContext).
        $outlets = $user->hasRole('OWNER')
            ? Outlet::query()->orderBy('code')->get()
            : $user->outlets()->orderBy('code')->get();

        return ['outlets' => $outlets, 'permissions' => $this->scopes->forUser($user)];
    }
}
