<?php

use App\Modules\Inventory\Stock\Http\Controllers\AdjustmentController;
use App\Modules\Inventory\Stock\Http\Controllers\OpeningStockController;
use Illuminate\Support\Facades\Route;

// Prefix /api/v1 dan middleware 'api' sudah dipasang ModuleServiceProvider.
Route::middleware(['auth', 'outlet.context'])->group(function (): void {
    // Opening Stock (FR-INV-001, API-INV-04)
    Route::post('/inventory/opening-stock', OpeningStockController::class)
        ->middleware('permission:inventory.adjust')
        ->name('inventory.opening-stock.store');

    // Stock Adjustments (FR-INV-003, API-INV-05 & API-INV-06)
    Route::get('/inventory/adjustments', [AdjustmentController::class, 'index'])
        ->middleware('permission:inventory.view')
        ->name('inventory.adjustments.index');

    Route::post('/inventory/adjustments', [AdjustmentController::class, 'store'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.adjustments.store');
});
