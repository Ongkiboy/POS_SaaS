<?php

namespace App\Modules\Platform\Rbac;

use App\Modules\Platform\Rbac\Listeners\SetPermissionTeamFromUser;
use App\Support\Module\BaseModule;
use Illuminate\Auth\Events\Authenticated;

class RbacModule extends BaseModule
{
    public function events(): array
    {
        return [
            Authenticated::class => [SetPermissionTeamFromUser::class],
        ];
    }
}
