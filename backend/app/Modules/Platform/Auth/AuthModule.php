<?php

namespace App\Modules\Platform\Auth;

use App\Support\Module\BaseModule;

class AuthModule extends BaseModule
{
    public function routes(): ?string
    {
        return __DIR__.'/routes.php';
    }
}
