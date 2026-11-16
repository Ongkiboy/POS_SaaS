<?php

namespace Database\Seeders;

use App\Modules\Platform\Rbac\Services\SeedBusinessRoles;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SeedBusinessRoles $seeder): void
    {
        $seeder->ensurePermissions();
    }
}
