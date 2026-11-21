<?php

namespace App\Modules\Platform\Auth\Http\Controllers;

use App\Modules\Platform\Auth\Actions\LoginUser;
use App\Modules\Platform\Auth\Data\LoginInput;
use App\Modules\Platform\Auth\Http\Requests\LoginRequest;
use App\Modules\Platform\Auth\Http\Resources\LoginResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

final class LoginController
{
    public function __invoke(LoginRequest $request, LoginUser $login, PermissionRegistrar $permissions): JsonResponse
    {
        if (! $request->hasSession()) {
            abort(403);
        }

        $user = $login->handle(LoginInput::fromValidated($request->validated()));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $permissions->setPermissionsTeamId($user->business_id);

        return ApiResponse::success(
            (new LoginResource($user))->resolve($request),
            'Login berhasil',
        );
    }
}
