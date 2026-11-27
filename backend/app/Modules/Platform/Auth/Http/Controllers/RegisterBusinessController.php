<?php

namespace App\Modules\Platform\Auth\Http\Controllers;

use App\Modules\Platform\Auth\Actions\RegisterBusiness;
use App\Modules\Platform\Auth\Data\RegisterBusinessInput;
use App\Modules\Platform\Auth\Http\Requests\RegisterBusinessRequest;
use App\Modules\Platform\Auth\Http\Resources\RegisteredBusinessResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class RegisterBusinessController
{
    public function __invoke(RegisterBusinessRequest $request, RegisterBusiness $register): JsonResponse
    {
        $result = $register->handle(RegisterBusinessInput::fromValidated($request->validated()));

        return ApiResponse::success(
            (new RegisteredBusinessResource($result))->resolve($request),
            'Business berhasil dibuat',
            status: 201,
        );
    }
}
