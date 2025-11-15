<?php

namespace Database\Factories;

use App\Models\CardFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardFlag>
 */
class CardFlagFactory extends Factory
{
    protected $model = CardFlag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Visa', 'Mastercard', 'Amex', 'Elo']),
        ];
    }
}
