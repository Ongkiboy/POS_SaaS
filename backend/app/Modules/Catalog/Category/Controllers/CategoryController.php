<?php

namespace App\Modules\Catalog\Category\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Modules\Catalog\Category\Actions\CreateCategory;
use App\Modules\Catalog\Category\Actions\DeleteCategory;
use App\Modules\Catalog\Category\Actions\ListCategories;
use App\Modules\Catalog\Category\Actions\UpdateCategory;
use App\Modules\Catalog\Category\Requests\StoreCategoryRequest;
use App\Modules\Catalog\Category\Requests\UpdateCategoryRequest;
use App\Modules\Catalog\Category\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request, ListCategories $action)
    {
        return CategoryResource::collection(
            $action->handle($request->query('status'), (int) $request->query('per_page', 15))
        );
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action): JsonResponse
    {
        $category = $action->handle($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $action): CategoryResource
    {
        return new CategoryResource($action->handle($category, $request->validated()));
    }

    public function destroy(Category $category, DeleteCategory $action): JsonResponse
    {
        $action->handle($category);

        return response()->json(null, 204);
    }
}