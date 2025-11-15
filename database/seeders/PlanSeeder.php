<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'BASIC_MONTHLY',
            'description' => 'Básico Mensal',
            'price' => 49.90,
            'periodicity' => 30,
            'active' => true,
        ]);

        Plan::create([
            'name' => 'BASIC_YEARLY',
            'description' => 'Básico Anual',
            'price' => 499.00,
            'periodicity' => 365,
            'active' => true,
        ]);

        Plan::create([
            'name' => 'PRO_MONTHLY',
            'description' => 'Pro Mensal',
            'price' => 99.90,
            'periodicity' => 30,
            'active' => true,
        ]);

        Plan::create([
            'name' => 'PRO_YEARLY',
            'description' => 'Pro Anual',
            'price' => 999.00,
            'periodicity' => 365,
            'active' => true,
        ]);
    }
}
