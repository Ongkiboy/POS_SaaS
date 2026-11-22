<?php

namespace App\Modules\Platform\Auth\Http\Resources;

use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Bentuk SDD API-AUTH-04. Hanya field yang tertulis di kontrak: tanpa password, token, atau status lockout.
 *
 * @property User $resource
 */
final class MeResource extends JsonResource
{
    /**
     * @param  Collection<int, Outlet>  $outlets
     * @param  list<array{code: string, scope: string|null}>  $permissions
     */
    public function __construct(
        User $user,
        private readonly Collection $outlets,
        private readonly array $permissions,
    ) {
        parent::__construct($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ],
            'outlets' => $this->outlets->map(fn (Outlet $outlet): array => [
                'id' => $outlet->id,
                'code' => $outlet->code,
                'name' => $outlet->name,
                'timezone' => $outlet->timezone,
            ])->values()->all(),
            'permissions' => $this->permissions,
        ];
    }
}
