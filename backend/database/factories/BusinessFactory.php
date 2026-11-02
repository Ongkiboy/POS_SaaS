<?php

namespace Database\Factories;

use App\Modules\Platform\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::limit($this->faker->company(), 80, '');

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'status' => 'ACTIVE',
        ];
    }
}
