<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected CouponService $service;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CouponService();
        $this->plan = Plan::factory()->create();
    }

    public function test_it_validates_a_valid_global_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'name' => 'GLOBAL',
            'plan_id' => null,
        ]);

        $result = $this->service->validate('GLOBAL', $this->plan->getKey());
        $this->assertEquals($coupon->id, $result->getKey());
    }

    public function test_it_validates_a_valid_plan_specific_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'name' => 'SPECIFIC',
            'plan_id' => $this->plan->getKey(),
        ]);

        $result = $this->service->validate('SPECIFIC', $this->plan->id);
        $this->assertEquals($coupon->id, $result->getKey());
    }

    public function test_it_rejects_a_coupon_for_a_different_plan(): void
    {
        $otherPlan = Plan::factory()->create();
        Coupon::factory()->create([
            'name' => 'SPECIFIC',
            'plan_id' => $otherPlan->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid coupon!');

        $this->service->validate('SPECIFIC', $this->plan->getKey());
    }

    public function test_it_rejects_a_non_existent_coupon(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid coupon!');

        $this->service->validate('BOGUS', $this->plan->getKey());
    }

    public function test_it_rejects_an_expired_coupon(): void
    {
        Carbon::setTestNow(Carbon::now()->subDays(10));
        $coupon = Coupon::factory()->create([
            'name' => 'EXPIRED',
            'expiration_days' => 5,
        ]);
        Carbon::setTestNow();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expired coupon!');

        $this->service->validate('EXPIRED', $this->plan->getKey());
    }

    public function test_it_rejects_a_coupon_that_has_reached_its_usage_limit(): void
    {
        $coupon = Coupon::factory()->create([
            'name' => 'USED_UP',
            'amount_of_uses' => 2,
        ]);

        Subscription::factory()->count(2)->create(['coupon_id' => $coupon->id]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Coupon usage limit exceeded!');

        $this->service->validate('USED_UP', $this->plan->getKey());
    }

    public function test_it_validates_a_coupon_under_its_usage_limit(): void
    {
        $coupon = Coupon::factory()->create([
            'name' => 'ONE_LEFT',
            'amount_of_uses' => 2,
        ]);

        Subscription::factory()->create(['coupon_id' => $coupon->id]);

        $result = $this->service->validate('ONE_LEFT', $this->plan->getKey());
        $this->assertEquals($coupon->id, $result->getKey());
    }
}
