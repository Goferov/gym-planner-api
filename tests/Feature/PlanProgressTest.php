<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Exercise;
use App\Models\ExerciseLog;
use App\Models\Plan;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use App\Models\PlanUser;
use App\Policies\PlanUserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;

class PlanProgressTest extends TestCase
{
    /**
     * A basic test example.
     */

    use RefreshDatabase;

    public function test_user_can_start_training_day(): void
    {
        $startDate = Carbon::parse('2025-05-19')->startOfDay();
        Carbon::setTestNow($startDate);
        $trainer = User::factory()->create(['role' => 'trainer']);

        $user = User::factory()->create(['role' => 'user', 'trainer_id'  => $trainer->id]);
        $token = auth()->login($user);

        $plan = Plan::factory()->create(['trainer_id' => $trainer->id]);
        $planUser = PlanUser::factory()->create([
            'plan_id'    => $plan->id,
            'user_id'    => $user->id,
            'started_at' => $startDate,
        ]);

        $day = PlanDay::factory()->create([
            'plan_id'     => $plan->id,
            'week_number' => 1,
            'day_number'  => 1,
        ]);

        $exercise = Exercise::factory()->create(['user_id' => $user->id]);

        $pde = PlanDayExercise::factory()->create([
            'plan_day_id' => $day->id,
            'exercise_id' => $exercise->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/plan-user/{$planUser->id}/day/start");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'week',
            'day',
            'exercises',
            'rest',
        ]);

        $response->assertJsonFragment([
            'week' => 1,
            'day'  => 1,
            'rest' => false,
        ]);

        $this->assertDatabaseHas('exercise_logs', [
            'plan_user_id' => $planUser->id,
            'plan_day_exercise_id' => $pde->id,
            'date' => $startDate->toDateString(),
        ]);
    }
}
