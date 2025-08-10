<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Plan;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use App\Models\PlanUser;
use App\Models\Exercise;

class TestWorkoutSeeder extends Seeder
{
    public function run(): void
    {
        /* ---------- 1. Konta użytkowników ---------- */
        $trainer = User::factory()->create([
            'name'     => 'John Trainer',
            'email'    => 'trainer@test.com',
            'role'     => 'trainer',
            'password' => Hash::make('password'),
        ]);

        $senior = User::factory()->create([
            'name'     => 'Senior Example',
            'email'    => 'senior@test.com',
            'role'     => 'user',
            'password' => Hash::make('password'),
        ]);

        /* ---------- 2. Ćwiczenie testowe ---------- */
        $exercise = Exercise::factory()->create([
            'user_id' => 1,              // ćwiczenia systemowe
            'name'    => 'March in place',
        ]);

        /* ---------- 3. Plan + dzień treningowy ---------- */
        $plan = Plan::factory()->create([
            'trainer_id' => $trainer->id,
            'name'       => 'Test 1-day plan',
            'duration_weeks' => 1,
        ]);

        $planDay = PlanDay::factory()->create([
            'plan_id'     => $plan->id,
            'week_number' => 1,
            'day_number'  => 1,
            'description' => 'Test day',
        ]);

        $planDay2 = PlanDay::factory()->create([
            'plan_id'     => $plan->id,
            'week_number' => 1,
            'day_number'  => 2,
            'description' => 'Test day',
        ]);

        PlanDayExercise::factory()->count(3)->create([
            'plan_day_id' => $planDay->id,
            'exercise_id' => $exercise->id,
        ]);

        PlanDayExercise::factory()->count(1)->create([
            'plan_day_id' => $planDay2->id,
            'exercise_id' => $exercise->id,
        ]);

        /* ---------- 4. Przypisanie seniora do planu ---------- */
        PlanUser::create([
            'plan_id' => $plan->id,
            'user_id' => $senior->id,
            'assigned_at' => now(),
            'started_at' => now(),
            'active'      => true,
        ]);
    }
}
