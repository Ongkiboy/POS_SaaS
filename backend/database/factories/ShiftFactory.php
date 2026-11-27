<?php

namespace Database\Factories;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\User\Models\User;
use App\Modules\Shift\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            // Outlet dan kasir dibuat di business yang SAMA dengan shift.
            'outlet_id' => fn (array $attributes) => Outlet::factory()->create(['business_id' => $attributes['business_id']])->id,
            'cashier_id' => fn (array $attributes) => User::factory()->create(['business_id' => $attributes['business_id']])->id,
            'status' => 'OPEN',
            'business_date' => now()->toDateString(),
            'opening_cash' => 200000,
            'opened_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'CLOSED', 'closed_at' => now()]);
    }
}
