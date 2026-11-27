<?php

namespace App\Modules\Inventory\Ingredient\Actions;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\IngredientUnitConversion;
use App\Modules\Inventory\Ingredient\Models\Unit;
use Illuminate\Support\Facades\DB;

final class CreateIngredient
{
    /**
     * @param  array{
     *     sku: string,
     *     name: string,
     *     base_unit_id: int|string,
     *     last_cost?: string|float|int|null,
     *     min_stock?: string|float|int|null,
     *     status?: string,
     * }  $data
     */
    public function handle(array $data): Ingredient
    {
        return DB::transaction(function () use ($data): Ingredient {
            $data['status'] = $data['status'] ?? 'ACTIVE';
            $data['last_cost'] = $data['last_cost'] ?? '0.0000';
            $data['min_stock'] = $data['min_stock'] ?? '0.0000';

            /** @var Ingredient $ingredient */
            $ingredient = Ingredient::query()->create($data);

            $this->createStandardConversions($ingredient);

            return $ingredient->load(['baseUnit', 'conversions.unit']);
        });
    }

    private function createStandardConversions(Ingredient $ingredient): void
    {
        $baseUnit = Unit::query()->find($ingredient->base_unit_id);
        if (! $baseUnit) {
            return;
        }

        // 1. Konversi base unit ke dirinya sendiri (faktor = 1)
        IngredientUnitConversion::query()->create([
            'business_id' => $ingredient->business_id,
            'ingredient_id' => $ingredient->id,
            'unit_id' => $baseUnit->id,
            'factor_to_base' => '1.000000',
        ]);

        // 2. Konversi dari config/units.php
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
