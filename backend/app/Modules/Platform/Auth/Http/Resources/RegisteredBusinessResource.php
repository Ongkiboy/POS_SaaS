<?php

namespace App\Modules\Platform\Auth\Http\Resources;

use App\Modules\Platform\Auth\Data\RegisteredBusiness;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RegisteredBusiness $resource
 */
final class RegisteredBusinessResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var RegisteredBusiness $result */
        $result = $this->resource;

        return [
            'business' => ['id' => $result->business->id, 'name' => $result->business->name],
            'outlet' => ['id' => $result->outlet->id, 'code' => $result->outlet->code],
            'user' => ['id' => $result->user->id, 'email' => $result->user->email, 'role' => 'OWNER'],
        ];
    }
}
