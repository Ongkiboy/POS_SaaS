<?php

namespace App\Modules\Platform\Rbac\Listeners;

use App\Modules\Platform\User\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Spatie\Permission\PermissionRegistrar;

/**
 * Setiap kali user terautentikasi, team Spatie = business user (ADR-005).
 */
final class SetPermissionTeamFromUser
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function handle(Authenticated $event): void
    {
        if ($event->user instanceof User) {
            $this->registrar->setPermissionsTeamId($event->user->business_id);
        }
    }
}
