<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CouponsControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plan = Plan::factory()->create();
    }

    public function test_it_can_list_all_coupons(): void
    {
        Coupon::factory()->count(3)->create();

        $this->getJson('/api/coupons')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_it_can_show_coupons_by_plan_id(): void
    {
        // Cupom global
        Coupon::factory()->create(['plan_id' => null]);
        // Cupom específico do plano
        Coupon::factory()->create(['plan_id' => $this->plan->id]);
        // Cupom de outro plano
        Coupon::factory()->create(['plan_id' => Plan::factory()->create()->id]);

        $response = $this->getJson("/api/coupons/{$this->plan->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_it_can_validate_a_valid_coupon_via_api(): void
    {
        Coupon::factory()->create([
            'name' => 'VALID',
            'plan_id' => $this->plan->id,
        ]);

        $response = $this->postJson('/api/coupons-validate', [
            'coupon' => 'VALID',
            'plan_id' => $this->plan->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'message' => 'Valid coupon!',
            ]);
    }

    public function test_it_rejects_an_invalid_coupon_via_api(): void
    {
        $response = $this->postJson('/api/coupons-validate', [
            'coupon' => 'INVALID',
            'plan_id' => $this->plan->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coupon');
    }

    public function test_it_rejects_an_expired_coupon_via_api(): void
    {
        Coupon::factory()->create(['name' => 'EXPIRED']);

        $service = $this->mock(CouponService::class);
        $service->shouldReceive('validate')
            ->andThrow(new \Exception('Expired coupon!'));

        $response = $this->postJson('/api/coupons-validate', [
            'coupon' => 'EXPIRED',
            'plan_id' => $this->plan->getKey(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'message' => 'Expired coupon!',
            ]);
    }
}
