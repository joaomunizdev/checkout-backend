<?php

namespace Tests\Feature;

use App\Models\CardFlag;
use App\Models\Plan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PlansCardFlagControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_can_list_all_plans(): void
    {
        Plan::factory()->count(3)->create();

        $response = $this->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_it_can_show_a_specific_plan(): void
    {
        $plan = Plan::factory()->create();

        $response = $this->getJson("/api/plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $plan->id]);
    }

    public function test_it_returns_404_for_missing_plan(): void
    {
        $this->getJson('/api/plans/999')
            ->assertStatus(404);
    }

    public function test_it_can_list_all_card_flags(): void
    {
        CardFlag::factory()->count(5)->create();

        $response = $this->getJson('/api/card-flags');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_it_can_show_a_specific_card_flag(): void
    {
        $flag = CardFlag::factory()->create();

        $response = $this->getJson("/api/card-flags/{$flag->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $flag->id]);
    }

    public function test_it_returns_404_for_missing_card_flag(): void
    {
        $this->getJson('/api/card-flags/999')
            ->assertStatus(404);
    }
}
