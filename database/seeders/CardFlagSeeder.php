<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CardFlag;

class CardFlagSeeder extends Seeder
{
    public function run(): void
    {

        CardFlag::create([
            'name' => 'Visa',
        ]);

        CardFlag::create([
            'name' => 'Mastercard',
        ]);

        CardFlag::create([
            'name' => 'Elo',
        ]);

        CardFlag::create([
            'name' => 'Amex',
        ]);
    }
}
