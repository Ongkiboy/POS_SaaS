<?php

namespace App\Modules\Platform\Auth\Http\Controllers;

use App\Modules\Platform\Auth\Actions\GetCurrentUser;
use App\Modules\Platform\Auth\Http\Resources\MeResource;
use App\Modules\Platform\User\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/me (SDD API-AUTH-04, FR-AUTH-004). Tanpa outlet.context: endpoint ini yang memberi daftar outlet.
 */
final class MeController
{
    public function __invoke(Request $request, GetCurrentUser $action): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $me = $action->handle($user);

        return ApiResponse::success(
            (new MeResource($user, $me['outlets'], $me['permissions']))->resolve($request),
        );
    }
}
