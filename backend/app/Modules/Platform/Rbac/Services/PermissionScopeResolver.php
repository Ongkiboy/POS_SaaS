<?php

namespace App\Modules\Platform\Rbac\Services;

use App\Modules\Platform\User\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menggabungkan permission Spatie dengan config/rbac_scopes.php (bentuk SDD API-AUTH-04).
 */
final class PermissionScopeResolver
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /**
     * @return list<array{code: string, scope: string|null}>
     */
    public function forUser(User $user): array
    {
        return $this->asTeamOf($user, function () use ($user): array {
            $scopes = $this->scopesForRole($user->getRoleNames()->first());

            return $user->getAllPermissions()
                ->pluck('name')
                ->sort()
                ->values()
                ->map(fn (string $code): array => ['code' => $code, 'scope' => $scopes[$code] ?? null])
                ->all();
        });
    }

    public function scopeFor(User $user, string $permission): ?string
    {
        return $this->asTeamOf(
            $user,
            fn (): ?string => $this->scopesForRole($user->getRoleNames()->first())[$permission] ?? null,
        );
    }

    /**
     * @return array<string, string>
     */
    private function scopesForRole(?string $role): array
    {
        if ($role === null) {
            return [];
        }

        /** @var array<string, array<string, string>> $all */
        $all = config('rbac_scopes', []);

        return $all[$role] ?? [];
    }

    /**
     * Spatie (teams) membaca role sesuai team aktif; pastikan team = business user.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function asTeamOf(User $user, callable $callback): mixed
    {
        $previous = $this->registrar->getPermissionsTeamId();
        $this->registrar->setPermissionsTeamId($user->business_id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        try {
            return $callback();
        } finally {
            $this->registrar->setPermissionsTeamId($previous);
        }
    }
}
