<?php

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_can_assign_plan_to_own_clients(): void
    {
        $trainer = User::factory()->create(['role' => 'trainer']);
        $client = User::factory()->create([
            'role' => 'user',
            'trainer_id' => $trainer->id,
        ]);

        $plan = Plan::factory()->create(['trainer_id' => $trainer->id]);

        $token = auth()->login($trainer);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/plans/{$plan->id}/assign", [
                'user_ids' => [$client->id],
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('plan_user', [
            'plan_id' => $plan->id,
            'user_id' => $client->id,
            'active' => true,
        ]);
    }
}
