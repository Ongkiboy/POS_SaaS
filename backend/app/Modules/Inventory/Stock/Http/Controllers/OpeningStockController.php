<?php

namespace App\Modules\Inventory\Stock\Http\Controllers;

use App\Modules\Inventory\Stock\Actions\RecordOpeningStock;
use App\Modules\Inventory\Stock\Http\Requests\StoreOpeningStockRequest;
use App\Modules\Inventory\Stock\Http\Resources\StockOpeningBatchResource;
use App\Modules\Platform\Tenant\OutletContext;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use LogicException;

final class OpeningStockController
{
    public function __invoke(
        StoreOpeningStockRequest $request,
        RecordOpeningStock $recordOpeningStock,
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

        /** @var string|null $note */
        $note = $request->validated('note');
        /** @var list<array{ingredient_id: int|string, quantity: numeric-string|string}> $items */
        $items = $request->validated('items');

        $result = $recordOpeningStock->handle(
            outletId: $outletId,
            userId: (int) $user->id,
            businessId: (int) $user->business_id,
            note: $note,
            items: $items,
        );

        return ApiResponse::success(
            (new StockOpeningBatchResource($result))->resolve($request),
            'Stok awal berhasil dicatat',
            status: 201,
        );
    }
}
