<?php

namespace Database\Factories;

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Sales\Order\Models\Order;
use App\Modules\Shift\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Order DRAFT kosong. Outlet, kasir, dan business_date diambil dari shift supaya selalu konsisten.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'shift_id' => fn (array $attributes) => Shift::factory()->create(['business_id' => $attributes['business_id']])->id,
            'outlet_id' => fn (array $attributes) => $this->shiftOf($attributes)->outlet_id,
            'cashier_id' => fn (array $attributes) => $this->shiftOf($attributes)->cashier_id,
            'business_date' => fn (array $attributes) => Carbon::parse($this->shiftOf($attributes)->business_date)->toDateString(),
            'order_number' => fn () => 'TST-'.$this->faker->unique()->numerify('######-####'),
            'order_type' => 'TAKEAWAY',
            'status' => 'DRAFT',
            'tax_rate_snapshot' => '10.00',
            'service_rate_snapshot' => '5.00',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function shiftOf(array $attributes): Shift
    {
        return Shift::query()->withoutGlobalScopes()->findOrFail($attributes['shift_id']);
    }
}
