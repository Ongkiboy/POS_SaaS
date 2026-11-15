<?php

namespace App\Modules\Platform\Auth\Actions;

use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Auth\Data\RegisterBusinessInput;
use App\Modules\Platform\Auth\Data\RegisteredBusiness;
use App\Modules\Platform\Auth\Http\Requests\RegisterBusinessRequest;
use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\Services\SeedBusinessRoles;
use App\Modules\Platform\Tenant\TenantContext;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * FR-AUTH-001 / SEQ-AUTH-01: business + outlet + Owner + user_outlets + 5 role dalam satu transaksi.
 */
final class RegisterBusiness
{
    private const EMAIL_UNIQUE_INDEX = 'users_email_lower_unique';

    public function __construct(
        private readonly TenantContext $tenant,
        private readonly SeedBusinessRoles $seedRoles,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function handle(RegisterBusinessInput $input): RegisteredBusiness
    {
        try {
            return DB::transaction(fn (): RegisteredBusiness => $this->create($input));
        } catch (UniqueConstraintViolationException $e) {
            // Dua request bersamaan dengan email sama: validasi lolos, index DB menolak.
            if (str_contains($e->getMessage(), self::EMAIL_UNIQUE_INDEX)) {
                throw ValidationException::withMessages(['email' => [RegisterBusinessRequest::EMAIL_TAKEN]]);
            }

            throw $e;
        }
    }

    private function create(RegisterBusinessInput $input): RegisteredBusiness
    {
        $business = Business::query()->create([
            'name' => $input->businessName,
            'slug' => $this->uniqueSlug($input->businessName),
            'status' => 'ACTIVE',
        ]);

        return $this->tenant->runAs($business->id, function () use ($business, $input): RegisteredBusiness {
            $outlet = Outlet::query()->create([
                'code' => $input->outletCode,
                'name' => $input->outletName,
                'timezone' => $input->timezone,
                'status' => 'ACTIVE',
            ]);

            $user = User::query()->create([
                'name' => $input->ownerName,
                'email' => $input->email,
                'password' => $input->password,
                'status' => 'ACTIVE',
            ]);

            // $user->outlets()->attach($outlet->id, ['business_id' => $business->id]);

            $this->seedRoles->run($business);
            $this->registrar->setPermissionsTeamId($business->id);
            $user->assignRole('OWNER');

            AuditLogger::log(
                'BUSINESS_REGISTERED',
                $business,
                null,
                [
                    'business' => ['name' => $business->name, 'slug' => $business->slug],
                    'outlet' => ['code' => $outlet->code, 'name' => $outlet->name, 'timezone' => $outlet->timezone],
                    'owner' => ['name' => $user->name, 'email' => $user->email],
                ],
                userId: $user->id,
                outletId: $outlet->id,
                businessId: $business->id,
            );

            return new RegisteredBusiness($business, $outlet, $user);
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::limit(Str::slug($name) ?: 'business', 110, '');
        $slug = $base;
        $suffix = 2;

        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
