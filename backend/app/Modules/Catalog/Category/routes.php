<?php

use App\Modules\Catalog\Category\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/categories')->middleware(['api'])->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});