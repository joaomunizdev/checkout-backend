<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\CouponType;
use App\Models\Plan;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $proMonthlyPlan = Plan::where('name', 'PRO_MONTHLY')->firstOrFail();
        $proYearlyPlan = Plan::where('name', 'PRO_YEARLY')->firstOrFail();
        $basicYearlyPlan = Plan::where('name', 'BASIC_YEARLY')->firstOrFail();

        Coupon::create([
            'name' => 'OFF10',
            'plan_id' => null,
            'expiration_days' => null,
            'amount_of_uses' => null,
            'discount_percent' => 10,
            'discount_amount' => null,
        ]);

        Coupon::create([
            'name' => 'SAVE30',
            'plan_id' => $proMonthlyPlan->getKey(),
            'expiration_days' => 5,
            'amount_of_uses' => 2,
            'discount_percent' => null,
            'discount_amount' => 30,
        ]);

        Coupon::create([
            'name' => 'YEAR20',
            'plan_id' => $proYearlyPlan->getKey(),
            'expiration_days' => 30,
            'amount_of_uses' => 5,
            'discount_percent' => 20,
            'discount_amount' => null,
        ]);

        Coupon::create([
            'name' => 'YEAR20',
            'plan_id' => $basicYearlyPlan->getKey(),
            'expiration_days' => 30,
            'amount_of_uses' => 5,
            'discount_percent' => 20,
            'discount_amount' => null,
        ]);

        Coupon::create([
            'name' => 'EXPIRED5',
            'expiration_days' => 1,
            'amount_of_uses' => null,
            'discount_percent' => null,
            'discount_amount' => 5.00,
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);
    }
}
