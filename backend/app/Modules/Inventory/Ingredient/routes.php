<?php

use App\Modules\Inventory\Ingredient\Http\Controllers\IngredientController;
use Illuminate\Support\Facades\Route;

// Prefix /api/v1 dan middleware 'api' sudah dipasang ModuleServiceProvider.
Route::middleware(['auth'])->group(function (): void {
    Route::get('/ingredients', [IngredientController::class, 'index'])
        ->middleware('permission:inventory.view')
        ->name('inventory.ingredients.index');

    Route::post('/ingredients', [IngredientController::class, 'store'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.ingredients.store');

    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show'])
        ->middleware('permission:inventory.view')
        ->name('inventory.ingredients.show');

    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.ingredients.update');

    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.ingredients.destroy');
});
