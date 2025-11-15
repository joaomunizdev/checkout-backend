<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'plan_id' => null,
            'expiration_days' => null,
            'amount_of_uses' => $this->faker->numberBetween(50, 200),
            'discount_percent' => $this->faker->numberBetween(5, 30),
            'discount_amount' => null,
        ];
    }

    public function withAmountDiscount(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_percent' => null,
                'discount_amount' => $this->faker->randomFloat(2, 5, 50),
            ];
        });
    }
    public function forPlan(Plan $plan): Factory
    {
        return $this->state(fn(array $attributes) => [
            'plan_id' => $plan->getKey(),
        ]);
    }
}
