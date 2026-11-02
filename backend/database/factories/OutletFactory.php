<?php

namespace Database\Factories;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('??##')),
            'name' => 'Outlet '.$this->faker->city(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'ACTIVE',
        ];
    }
}
