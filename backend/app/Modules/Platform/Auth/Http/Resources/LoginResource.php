<?php

namespace App\Modules\Platform\Auth\Http\Resources;

use App\Modules\Platform\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
final class LoginResource extends JsonResource
{
    /**
     * Membaca role butuh team Spatie = business user; controller sudah mengaturnya.
     *
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
                'role' => $user->getRoleNames()->first(),
            ],
        ];
    }
}
