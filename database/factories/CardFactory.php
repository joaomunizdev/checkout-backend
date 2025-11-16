<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        return [
            'card_number' => $this->faker->creditCardNumber(),
            'client_name' => $this->faker->name(),
            'last_4_digits' => $this->faker->numberBetween(1,4),
            'expire_date' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'cvc' => $this->faker->numberBetween(100, 999),
            'card_flag_id' => CardFlag::factory(),
        ];
    }
}
