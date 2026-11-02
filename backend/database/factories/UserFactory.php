<?php

namespace Database\Factories;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Rahasia123',
            'status' => 'ACTIVE',
        ];
    }
}
