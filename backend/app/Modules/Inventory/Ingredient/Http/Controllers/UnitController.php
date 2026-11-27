<?php

namespace App\Modules\Inventory\Ingredient\Http\Controllers;

use App\Modules\Inventory\Ingredient\Http\Resources\UnitResource;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UnitController
{
    public function index(): JsonResponse
    {
        $units = Unit::query()->orderBy('code')->get();

        return ApiResponse::success(
            UnitResource::collection($units),
        );
    }
}
