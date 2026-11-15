<?php

namespace App\Modules\Platform\Auth\Data;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;

final readonly class RegisteredBusiness
{
    public function __construct(
        public Business $business,
        public Outlet $outlet,
        public User $user,
    ) {}
}
