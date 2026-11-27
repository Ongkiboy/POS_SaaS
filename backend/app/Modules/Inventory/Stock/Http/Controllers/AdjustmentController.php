<?php

namespace App\Modules\Inventory\Stock\Http\Controllers;

use App\Modules\Inventory\Stock\Actions\RecordAdjustment;
use App\Modules\Inventory\Stock\Http\Requests\StoreAdjustmentRequest;
use App\Modules\Inventory\Stock\Http\Resources\StockAdjustmentResource;
use App\Modules\Inventory\Stock\Models\StockAdjustment;
use App\Modules\Platform\Tenant\OutletContext;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

final class AdjustmentController
{
    public function index(Request $request, OutletContext $outletContext): JsonResponse
    {
        $outletId = $outletContext->id();
        if ($outletId === null) {
            throw new LogicException('Outlet aktif belum dipilih.');
        }

        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        $query = StockAdjustment::query()
            ->forOutlet($outletId)
            ->with(['ingredient', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', (int) $request->query('ingredient_id'));
        }

        $page = $query->paginate($perPage);

        return ApiResponse::success(
            StockAdjustmentResource::collection($page->items()),
            meta: [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        );
    }

    public function store(
        StoreAdjustmentRequest $request,
        RecordAdjustment $recordAdjustment,
        OutletContext $outletContext,
    ): JsonResponse {
        $user = $request->user();
        if ($user === null) {
            throw new LogicException('User belum terautentikasi.');
        }

        $outletId = $outletContext->id();
        if ($outletId === null) {
            throw new LogicException('Outlet aktif belum dipilih.');
        }

        /** @var int|string $ingredientId */
        $ingredientId = $request->validated('ingredient_id');
        /** @var string $direction */
        $direction = $request->validated('direction');
        /** @var numeric-string|string $quantity */
        $quantity = $request->validated('quantity');
        /** @var string $reason */
        $reason = $request->validated('reason');

        $adjustment = $recordAdjustment->handle(
            outletId: $outletId,
            userId: (int) $user->id,
            businessId: (int) $user->business_id,
            ingredientId: (int) $ingredientId,
            direction: $direction,
            quantity: (string) $quantity,
            reason: $reason,
        );

        return ApiResponse::success(
            (new StockAdjustmentResource($adjustment))->resolve($request),
            'Penyesuaian stok berhasil dicatat',
            status: 201,
        );
    }
}
