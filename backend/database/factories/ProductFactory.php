<?php

namespace Database\Factories;

use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            // Kategori dibuat di business yang SAMA dengan produk.
            'category_id' => fn (array $attributes) => Category::factory()->create(['business_id' => $attributes['business_id']])->id,
            'sku' => 'SKU-'.$this->faker->unique()->numerify('#####'),
            'name' => 'Produk '.$this->faker->unique()->numerify('####'),
            'selling_price' => 18000,
            'status' => 'ACTIVE',
        ];
    }
}
