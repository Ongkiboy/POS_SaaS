<?php

use App\Modules\Inventory\Stock\Http\Controllers\OpeningStockController;
use Illuminate\Support\Facades\Route;

// Prefix /api/v1 dan middleware 'api' sudah dipasang ModuleServiceProvider.
Route::middleware(['auth', 'outlet.context'])->group(function (): void {
    Route::post('/inventory/opening-stock', OpeningStockController::class)
        ->middleware('permission:inventory.adjust')
        ->name('inventory.opening-stock.store');
});
