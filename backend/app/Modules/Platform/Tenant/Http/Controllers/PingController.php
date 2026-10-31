<?php

namespace App\Modules\Platform\Tenant\Http\Controllers;

use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PingController
{
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success(['pong' => true]);
    }
}
