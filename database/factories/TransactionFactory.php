<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'subscription_id' => Subscription::factory(),
            'status' => $this->faker->boolean(80),
        ];
    }
}
