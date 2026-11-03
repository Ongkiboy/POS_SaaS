<?php

namespace Database\Factories;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Platform\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            // Base unit 'g'; dibuat bila belum ada (test tidak selalu menjalankan UnitSeeder).
            'base_unit_id' => fn () => Unit::query()->firstOrCreate(['code' => 'g'], ['name' => 'Gram'])->id,
            'sku' => 'ING-'.$this->faker->unique()->numerify('#####'),
            'name' => 'Bahan '.$this->faker->unique()->numerify('####'),
            'last_cost' => '0',
            'min_stock' => '0',
            'status' => 'ACTIVE',
        ];
    }
}
