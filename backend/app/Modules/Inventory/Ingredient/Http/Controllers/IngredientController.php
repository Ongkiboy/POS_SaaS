<?php

namespace App\Modules\Inventory\Ingredient\Http\Controllers;

use App\Modules\Inventory\Ingredient\Actions\CreateIngredient;
use App\Modules\Inventory\Ingredient\Actions\DeleteIngredient;
use App\Modules\Inventory\Ingredient\Actions\UpdateIngredient;
use App\Modules\Inventory\Ingredient\Http\Requests\StoreIngredientRequest;
use App\Modules\Inventory\Ingredient\Http\Requests\UpdateIngredientRequest;
use App\Modules\Inventory\Ingredient\Http\Resources\IngredientResource;
use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IngredientController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        $query = Ingredient::query()
            ->with(['baseUnit', 'conversions.unit'])
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $page = $query->paginate($perPage);

        return ApiResponse::success(
            IngredientResource::collection($page->items()),
            meta: [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        );
    }

    public function store(StoreIngredientRequest $request, CreateIngredient $action): JsonResponse
    {
        $ingredient = $action->handle($request->validated());

        return ApiResponse::success(
            new IngredientResource($ingredient),
            message: 'Bahan berhasil ditambahkan.',
            status: 201,
        );
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        return ApiResponse::success(
            new IngredientResource($ingredient->load(['baseUnit', 'conversions.unit'])),
        );
    }

    public function update(
        UpdateIngredientRequest $request,
        Ingredient $ingredient,
        UpdateIngredient $action,
    ): JsonResponse {
        $updated = $action->handle($ingredient, $request->validated());

        return ApiResponse::success(
            new IngredientResource($updated),
            message: 'Bahan berhasil diperbarui.',
        );
    }

    public function destroy(Ingredient $ingredient, DeleteIngredient $action): JsonResponse
    {
        $action->handle($ingredient);

        return ApiResponse::success(
            null,
            message: 'Bahan berhasil dihapus.',
        );
    }
}
