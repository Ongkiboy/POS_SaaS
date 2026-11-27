<?php

namespace App\Modules\Inventory\Stock\Actions;

use App\Modules\Inventory\Stock\Data\StockChange;
use App\Modules\Inventory\Stock\Models\StockOpeningBatch;
use App\Modules\Inventory\Stock\Services\StockService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

final class RecordOpeningStock
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * @param  list<array{ingredient_id: int|string, quantity: numeric-string|string}>  $items
     * @return array{
     *     batch_id: int,
     *     items: list<array{ingredient_id: int, quantity_after: string}>
     * }
     */
    public function handle(
        int $outletId,
        int $userId,
        int $businessId,
        ?string $note,
        array $items,
    ): array {
        return DB::transaction(function () use ($outletId, $userId, $businessId, $note, $items): array {
            // SEQ-INV-01: Record induk dibuat lebih dulu agar reference_id tersedia.
            /** @var StockOpeningBatch $batch */
            $batch = StockOpeningBatch::query()->create([
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'note' => $note,
            ]);

            $changes = [];
            foreach ($items as $item) {
                $changes[] = new StockChange(
                    type: 'OPENING',
                    outletId: $outletId,
                    ingredientId: (int) $item['ingredient_id'],
                    quantityDelta: (string) $item['quantity'],
                    referenceType: 'OPENING',
                    referenceId: (int) $batch->id,
                    userId: $userId,
                    note: $note,
                );
            }

            // Eksekusi mutasi stok berurutan untuk mencegah deadlock (ADR-002).
            $movements = $this->stockService->applyMany($changes);

            $movementsByIngredient = [];
            foreach ($movements as $movement) {
                $movementsByIngredient[(int) $movement->ingredient_id] = (string) $movement->quantity_after;
            }

            $resultItems = [];
            foreach ($items as $item) {
                $ingId = (int) $item['ingredient_id'];
                $resultItems[] = [
                    'ingredient_id' => $ingId,
                    'quantity_after' => $movementsByIngredient[$ingId] ?? '0.0000',
                ];
            }

            // Catat audit log OPENING_STOCK (afterCommit).
            AuditLogger::log(
                action: 'OPENING_STOCK',
                resource: $batch,
                before: null,
                after: [
                    'batch_id' => (int) $batch->id,
                    'items' => $resultItems,
                    'note' => $note,
                ],
                outletId: $outletId,
                businessId: $businessId,
                userId: $userId,
            );

            return [
                'batch_id' => (int) $batch->id,
                'items' => $resultItems,
            ];
        });
    }
}
