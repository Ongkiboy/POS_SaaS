<?php

namespace App\Modules\Inventory\Stock\Services;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Stock\Data\StockChange;
use App\Modules\Inventory\Stock\Models\StockBalance;
use App\Modules\Inventory\Stock\Models\StockMovement;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Support\Exceptions\InsufficientStock;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * SATU-SATUNYA pintu mutasi stok buku besar (ADR-002, ACT-INV-01).
 * Mengunci balance FOR UPDATE, menghitung saldo before/after,
 * menegakkan aturan allow_negative_stock, dan mencatat ke stock_movements.
 */
class StockService
{
    /**
     * Terapkan satu perubahan stok (ACT-INV-01).
     */
    public function apply(
        StockChange|string $type,
        ?int $outletId = null,
        ?int $ingredientId = null,
        string|float|int|null $quantityDelta = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        string|float|int|null $unitCost = null,
        ?int $userId = null,
        ?string $note = null,
        bool $forceNegative = false,
        ?DateTimeInterface $occurredAt = null,
    ): StockMovement {
        $change = $type instanceof StockChange
            ? $type
            : new StockChange(
                type: $type,
                outletId: (int) $outletId,
                ingredientId: (int) $ingredientId,
                quantityDelta: $this->formatQuantity($quantityDelta),
                referenceType: (string) $referenceType,
                referenceId: (int) $referenceId,
                unitCost: $unitCost !== null ? $this->formatQuantity($unitCost) : null,
                userId: $userId,
                note: $note,
                forceNegative: $forceNegative,
                occurredAt: $occurredAt,
            );

        return DB::transaction(function () use ($change): StockMovement {
            // ACT-INV-01 Step B: Idempotensi movement (BR-009).
            $existing = StockMovement::query()
                ->where('outlet_id', $change->outletId)
                ->where('ingredient_id', $change->ingredientId)
                ->where('type', $change->type)
                ->where('reference_type', $change->referenceType)
                ->where('reference_id', $change->referenceId)
                ->first();

            if ($existing) {
                return $existing;
            }

            // ACT-INV-01 Step A: Kunci balance FOR UPDATE (buat jika belum ada).
            $balance = StockBalance::query()
                ->where('outlet_id', $change->outletId)
                ->where('ingredient_id', $change->ingredientId)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                // Buat balance awal 0 dengan penanganan race condition (firstOrCreate).
                StockBalance::query()->firstOrCreate(
                    [
                        'outlet_id' => $change->outletId,
                        'ingredient_id' => $change->ingredientId,
                    ],
                    [
                        'quantity' => '0.0000',
                    ]
                );

                $balance = StockBalance::query()
                    ->where('outlet_id', $change->outletId)
                    ->where('ingredient_id', $change->ingredientId)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $before = $this->formatQuantity($balance->quantity);
            $delta = $this->formatQuantity($change->quantityDelta);
            $after = bcadd($before, $delta, 4);

            // ACT-INV-01 Step D & E: Evaluasi stok minus.
            if (bccomp($after, '0.0000', 4) < 0) {
                $outlet = Outlet::query()->find($change->outletId);
                $allowNegative = $outlet !== null ? (bool) $outlet->allow_negative_stock : true;

                // Tolak jika minus dilarang dan bukan override settlement gateway (BR-011, BR-041).
                if (! $allowNegative && ! $change->forceNegative) {
                    $ingredient = Ingredient::query()->find($change->ingredientId);
                    $name = $ingredient !== null ? $ingredient->name : (string) $change->ingredientId;

                    throw InsufficientStock::forIngredient($name);
                }
            }

            // Snapshot unit cost: pakai nilai dari input atau snapshot last_cost bahan saat ini.
            $snapshotCost = $change->unitCost;
            if ($snapshotCost === null) {
                $ingredient = Ingredient::query()->find($change->ingredientId);
                $snapshotCost = $ingredient !== null ? $this->formatQuantity($ingredient->last_cost) : '0.0000';
            }

            // ACT-INV-01 Step F: INSERT movement + UPDATE balance.
            $movement = StockMovement::create([
                'outlet_id' => $change->outletId,
                'ingredient_id' => $change->ingredientId,
                'type' => $change->type,
                'quantity_delta' => $delta,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_cost' => $snapshotCost,
                'reference_type' => $change->referenceType,
                'reference_id' => $change->referenceId,
                'user_id' => $change->userId,
                'note' => $change->note,
                'occurred_at' => $change->occurredAt ?? now(),
            ]);

            $balance->update([
                'quantity' => $after,
                'last_movement_id' => $movement->id,
            ]);

            return $movement;
        });
    }

    /**
     * Terapkan banyak perubahan dalam satu transaksi dengan pengurutan ingredient_id (ADR-002).
     * Mencegah deadlock saat potongan stok banyak bahan.
     *
     * @param  array<int, StockChange>  $changes
     * @return array<int, StockMovement>
     */
    public function applyMany(array $changes): array
    {
        if ($changes === []) {
            return [];
        }

        // Urutkan berdasarkan ingredient_id ascending untuk mencegah deadlock (ADR-002).
        usort($changes, fn (StockChange $a, StockChange $b) => $a->ingredientId <=> $b->ingredientId);

        return DB::transaction(function () use ($changes): array {
            $movements = [];
            foreach ($changes as $change) {
                $movements[] = $this->apply($change);
            }

            return $movements;
        });
    }

    /**
     * Ambil kuantitas saldo stok saat ini dalam format 4 desimal string.
     */
    public function getBalance(int $outletId, int $ingredientId): string
    {
        $balance = StockBalance::query()
            ->where('outlet_id', $outletId)
            ->where('ingredient_id', $ingredientId)
            ->first();

        return $balance !== null ? $this->formatQuantity($balance->quantity) : '0.0000';
    }

    /**
     * Format nilai desimal presisi 4 digit tanpa pembulatan float (ERD §7.2).
     */
    private function formatQuantity(string|float|int|null $value): string
    {
        if ($value === null || $value === '') {
            return '0.0000';
        }

        if (is_string($value) && preg_match('/^-?\d+(\.\d{1,4})?$/', $value) === 1) {
            $parts = explode('.', $value);
            $dec = isset($parts[1]) ? str_pad(substr($parts[1], 0, 4), 4, '0') : '0000';

            return $parts[0].'.'.$dec;
        }

        return number_format((float) $value, 4, '.', '');
    }
}
