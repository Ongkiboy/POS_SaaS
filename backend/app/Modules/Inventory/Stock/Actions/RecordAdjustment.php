<?php

namespace App\Modules\Inventory\Stock\Actions;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Stock\Models\StockAdjustment;
use App\Modules\Inventory\Stock\Models\StockBalance;
use App\Modules\Inventory\Stock\Services\StockService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Support\Exceptions\InsufficientStock;
use Illuminate\Support\Facades\DB;

final class RecordAdjustment
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function handle(
        int $outletId,
        int $userId,
        int $businessId,
        int $ingredientId,
        string $direction,
        string $quantity,
        string $reason,
    ): StockAdjustment {
        return DB::transaction(function () use (
            $outletId,
            $userId,
            $businessId,
            $ingredientId,
            $direction,
            $quantity,
            $reason,
        ): StockAdjustment {
            // Pastikan baris saldo ada dan kunci untuk pembaruan (ADR-002)
            StockBalance::query()->firstOrCreate(
                [
                    'outlet_id' => $outletId,
                    'ingredient_id' => $ingredientId,
                ],
                [
                    'quantity' => '0.0000',
                ]
            );

            /** @var StockBalance $balance */
            $balance = StockBalance::query()
                ->where('outlet_id', $outletId)
                ->where('ingredient_id', $ingredientId)
                ->lockForUpdate()
                ->firstOrFail();

            $before = (string) $balance->quantity;

            // Tentukan tanda kuantitas delta
            $isIncrease = in_array(strtoupper($direction), ['INCREASE', 'TAMBAH'], true);
            $delta = $isIncrease
                ? bcadd($quantity, '0', 4)
                : bcmul($quantity, '-1', 4);

            $after = bcadd($before, $delta, 4);

            // Validasi stok minus jika pengurangan dan minus dilarang (FR-INV-003, BR-016)
            if (bccomp($after, '0.0000', 4) < 0) {
                $outlet = Outlet::query()->find($outletId);
                $allowNegative = $outlet !== null ? (bool) $outlet->allow_negative_stock : true;

                if (! $allowNegative) {
                    $ingredient = Ingredient::query()->find($ingredientId);
                    $name = $ingredient !== null ? $ingredient->name : (string) $ingredientId;

                    throw new InsufficientStock('Stok tidak mencukupi untuk pengurangan ini.', [
                        'ingredients' => [$name],
                    ]);
                }
            }

            // SEQ-INV-01: Record induk dibuat lebih dulu agar reference_id sudah ada
            /** @var StockAdjustment $adjustment */
            $adjustment = StockAdjustment::query()->create([
                'outlet_id' => $outletId,
                'ingredient_id' => $ingredientId,
                'quantity_before' => $before,
                'quantity_delta' => $delta,
                'quantity_after' => $after,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            // Buat movement ADJUSTMENT dan mutasikan saldo secara atomik
            $this->stockService->apply(
                type: 'ADJUSTMENT',
                outletId: $outletId,
                ingredientId: $ingredientId,
                quantityDelta: $delta,
                referenceType: 'ADJUSTMENT',
                referenceId: (int) $adjustment->id,
                userId: $userId,
                note: $reason,
            );

            // Catat audit log STOCK_ADJUSTMENT (afterCommit)
            AuditLogger::log(
                action: 'STOCK_ADJUSTMENT',
                resource: $adjustment,
                before: [
                    'quantity' => $before,
                ],
                after: [
                    'id' => (int) $adjustment->id,
                    'ingredient_id' => (int) $adjustment->ingredient_id,
                    'quantity_before' => $before,
                    'quantity_delta' => $delta,
                    'quantity_after' => $after,
                    'reason' => $reason,
                ],
                reason: $reason,
                outletId: $outletId,
                businessId: $businessId,
                userId: $userId,
            );

            return $adjustment;
        });
    }
}
