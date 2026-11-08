<?php

namespace App\Modules\Platform\Tenant;

use App\Support\Module\BaseModule;

class TenantModule extends BaseModule
{
    public function routes(): ?string
    {
        return __DIR__.'/routes.php';
    }
}
