<?php

namespace App\Modules\Platform\Rbac\Services;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Rbac\PermissionMatrix;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedBusinessRoles
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /** Permission bersifat global; aman dipanggil berulang. */
    public function ensurePermissions(): void
    {
        foreach (PermissionMatrix::PERMISSIONS as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->registrar->forgetCachedPermissions();
    }

    /** Lima role untuk satu business, dengan permission sesuai matriks. Idempoten. */
    public function run(Business $business): void
    {
        $this->ensurePermissions();

        foreach (PermissionMatrix::rolePermissions() as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate([
                'business_id' => $business->id,
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }

        $this->registrar->forgetCachedPermissions();
    }
}
