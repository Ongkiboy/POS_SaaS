<?php

namespace App\Modules\Platform\Rbac\Services;

use App\Modules\Platform\User\Models\User;

/**
 * Aturan "Own/Limited" (SRS §2.1, BR-024, BR-025) yang dipakai Policy per entitas
 * (OrderPolicy, UserPolicy, dst.). Policy entitas memanggil method di sini.
 */
final class AccessRules
{
    /** Role yang boleh dikelola Manager (FR-USER-001, BR-024). */
    public const MANAGEABLE_BY_MANAGER = ['CASHIER', 'INVENTORY', 'KITCHEN'];

    public function __construct(private readonly PermissionScopeResolver $scopes) {}

    /**
     * Akses ke record yang punya pemilik (mis. order milik kasir).
     * Scope OWN_ONLY/LIMITED berarti hanya record milik actor.
     */
    public function canAccessOwned(User $actor, string $permission, ?int $ownerUserId): bool
    {
        if (! $actor->checkPermissionTo($permission)) {
            return false;
        }

        if ($this->scopes->scopeFor($actor, $permission) === null) {
            return true;
        }

        return $ownerUserId !== null && $ownerUserId === (int) $actor->getKey();
    }

    /** Owner semua role; Manager hanya CASHIER/INVENTORY/KITCHEN; role lain tidak. */
    public function canManageRole(User $actor, string $targetRole): bool
    {
        if ($actor->hasRole('OWNER')) {
            return true;
        }

        if ($actor->hasRole('MANAGER')) {
            return in_array($targetRole, self::MANAGEABLE_BY_MANAGER, true);
        }

        return false;
    }

    /**
     * Manager hanya boleh menugaskan outlet yang ia akses; Owner semua outlet business.
     *
     * @param  list<int>  $outletIds
     */
    public function canAssignOutlets(User $actor, array $outletIds): bool
    {
        if ($actor->hasRole('OWNER')) {
            return true;
        }

        $own = $actor->outlets()->pluck('outlets.id')->map(fn ($id): int => (int) $id)->all();

        return array_diff($outletIds, $own) === [];
    }
}
