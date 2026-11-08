<?php

namespace Database\Factories;

use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Platform\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => 'Kategori '.$this->faker->unique()->numerify('####'),
            'status' => 'ACTIVE',
            'sort_order' => 0,
        ];
    }
}
