<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionsControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected Plan $plan;
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plan = Plan::factory()->create(['price' => 100.00]);
        $this->headers = ['Idempotency-Key' => (string) Str::uuid()];
    }

    private function getPayload(array $overrides = []): array
    {
        return array_merge([
            'plan_id' => $this->plan->id,
            'email' => 'test@example.com',
            'coupon' => null,
        ], $overrides);
    }

    public function test_it_shows_create_payload_format(): void
    {
        $this->getJson('/api/subscriptions/create')
            ->assertStatus(200)
            ->assertJsonStructure(['payload_format']);
    }

    public function test_it_can_create_a_subscription_without_a_coupon(): void
    {
        $payload = $this->getPayload();

        $response = $this->postJson('/api/subscriptions', $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                'plan_id' => $this->plan->id,
                'email' => 'test@example.com',
                'price_paid' => 100.00,
            ]);

        $this->assertDatabaseHas('subscriptions', ['email' => 'test@example.com']);
    }

    public function test_it_can_create_a_subscription_with_percent_coupon(): void
    {
        Coupon::factory()->create([
            'name' => 'PERC30',
            'plan_id' => $this->plan->id,
            'discount_percent' => 30,
        ]);

        $payload = $this->getPayload(['coupon' => 'PERC30']);
        $response = $this->postJson('/api/subscriptions', $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJson(['price_paid' => 70.00]);
    }

    public function test_it_can_create_a_subscription_with_amount_coupon(): void
    {
        Coupon::factory()->create([
            'name' => 'AMT10',
            'plan_id' => $this->plan->getKey(),
            'discount_amount' => 10.00,
            'discount_percent' => null,
        ]);

        $payload = $this->getPayload(['coupon' => 'AMT10']);
        $response = $this->postJson('/api/subscriptions', $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJson(['price_paid' => 90.00]);
    }

    public function test_it_rejects_creation_with_a_duplicate_email(): void
    {
        Subscription::factory()->create(['email' => 'test@example.com']);

        $payload = $this->getPayload();
        $response = $this->postJson('/api/subscriptions', $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Este e-mail já possui uma assinatura. Por favor, utilize outro e-mail.'
            ]);
    }

    public function test_it_rejects_creation_with_an_invalid_coupon(): void
    {
        $payload = $this->getPayload(['coupon' => 'INVALID']);

        $response = $this->postJson('/api/subscriptions', $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coupon');
    }

    public function test_it_rejects_creation_without_idempotency_key(): void
    {
        $payload = $this->getPayload();
        $response = $this->postJson('/api/subscriptions', $payload);

        $response->assertStatus(400);
    }

    public function test_it_can_list_all_subscriptions(): void
    {
        Subscription::factory()->count(3)->create();

        $this->getJson('/api/subscriptions')
            ->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([['plan', 'coupon', 'transaction']]);
    }

    public function test_it_can_show_a_specific_subscription(): void
    {
        $subscription = Subscription::factory()->create();

        $this->getJson("/api/subscriptions/{$subscription->id}")
            ->assertStatus(200)
            ->assertJson(['id' => $subscription->id])
            ->assertJsonStructure(['plan', 'coupon', 'transaction']);
    }

    public function test_it_returns_404_for_missing_subscription(): void
    {
        $this->getJson('/api/subscriptions/999')
            ->assertStatus(404);
    }
}
