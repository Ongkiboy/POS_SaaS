<?php

namespace App\Modules\Inventory\Ingredient\Actions;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\IngredientUnitConversion;
use App\Modules\Inventory\Ingredient\Models\Unit;
use Illuminate\Support\Facades\DB;

final class UpdateIngredient
{
    /**
     * @param  array{
     *     sku?: string,
     *     name?: string,
     *     base_unit_id?: int|string,
     *     last_cost?: string|float|int|null,
     *     min_stock?: string|float|int|null,
     *     status?: string,
     * }  $data
     */
    public function handle(Ingredient $ingredient, array $data): Ingredient
    {
        return DB::transaction(function () use ($ingredient, $data): Ingredient {
            $baseUnitChanged = isset($data['base_unit_id']) && (int) $data['base_unit_id'] !== (int) $ingredient->base_unit_id;

            $ingredient->update($data);

            if ($baseUnitChanged) {
                // Hapus konversi lama dan buat ulang sesuai base unit baru
                IngredientUnitConversion::query()
                    ->where('ingredient_id', $ingredient->id)
                    ->delete();

                $this->createStandardConversions($ingredient);
            }

            return $ingredient->fresh(['baseUnit', 'conversions.unit']);
        });
    }

    private function createStandardConversions(Ingredient $ingredient): void
    {
        $baseUnit = Unit::query()->find($ingredient->base_unit_id);
        if (! $baseUnit) {
            return;
        }

        IngredientUnitConversion::query()->create([
            'business_id' => $ingredient->business_id,
            'ingredient_id' => $ingredient->id,
            'unit_id' => $baseUnit->id,
            'factor_to_base' => '1.000000',
        ]);

        /** @var array<string, array{base: string, factor: string}> $conversions */
        $conversions = config('units.conversions', []);

        foreach ($conversions as $unitCode => $config) {
            if ($config['base'] === $baseUnit->code) {
                $targetUnit = Unit::query()->where('code', $unitCode)->first();
                if ($targetUnit) {
                    IngredientUnitConversion::query()->create([
                        'business_id' => $ingredient->business_id,
                        'ingredient_id' => $ingredient->id,
                        'unit_id' => $targetUnit->id,
                        'factor_to_base' => $config['factor'],
                    ]);
                }
            }
        }
    }
}
