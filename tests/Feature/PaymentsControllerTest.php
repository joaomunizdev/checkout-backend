<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\CardFlag;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected Subscription $subscription;
    protected CardFlag $cardFlag;
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscription = Subscription::factory()->create(['active' => false]);
        $this->cardFlag = CardFlag::factory()->create();
        $this->headers = ['Idempotency-Key' => (string) Str::uuid()];
    }

    private function getPayload(array $overrides = []): array
    {
        return array_merge([
            'subscription_id' => $this->subscription->id,
            'card_number' => '5555444433332222',
            'client_name' => 'JOAO DA SILVA',
            'expire_date' => '12/28',
            'cvc' => '123',
            'card_flag_id' => $this->cardFlag->id,
        ], $overrides);
    }

    public function test_it_shows_create_payload_format(): void
    {
        $this->getJson('/api/payments/create')
            ->assertStatus(200)
            ->assertJsonStructure(['payload_format']);
    }

    public function test_it_can_process_a_successful_payment(): void
    {
        $payload = $this->getPayload();

        $response = $this->postJson('/api/payments', $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $this->subscription->id,
            'status' => true,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $this->subscription->id,
            'active' => true,
        ]);
    }

    public function test_it_handles_a_declined_payment(): void
    {
        $payload = $this->getPayload(['card_number' => '4444555533332222']);

        $response = $this->postJson('/api/payments', $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJson(['status' => false]);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $this->subscription->id,
            'status' => false,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $this->subscription->id,
            'active' => false,
        ]);
    }

    public function test_it_rejects_payment_for_an_already_active_subscription(): void
    {
        $this->subscription->update(['active' => true]);

        $payload = $this->getPayload();
        $response = $this->postJson('/api/payments', $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Esta assinatura já está ativa.']);
    }

    public function test_it_creates_a_new_card_if_it_doesnt_exist(): void
    {
        $this->assertDatabaseCount('cards', 0);
        $payload = $this->getPayload(['card_number' => '5111222233334444']);

        $this->postJson('/api/payments', $payload, $this->headers);

        $this->assertDatabaseCount('cards', 1);
        $this->assertDatabaseHas('cards', ['card_number' => '5111222233334444']);
    }

    public function test_it_uses_an_existing_card_if_it_exists(): void
    {
        $card = Card::factory()->create(['card_number' => '5222333344445555']);
        $this->assertDatabaseCount('cards', 1);

        $payload = $this->getPayload(['card_number' => '5222333344445555']);

        $this->postJson('/api/payments', $payload, $this->headers);

        $this->assertDatabaseCount('cards', 1);
        $this->assertDatabaseHas('transactions', ['card_id' => $card->id]);
    }

    public function test_it_rejects_payment_without_idempotency_key(): void
    {
        $payload = $this->getPayload();

        $response = $this->postJson('/api/payments', $payload);

        $response->assertStatus(400);
    }
}
