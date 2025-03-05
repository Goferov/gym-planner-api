<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanUser>
 */
class PlanUserFactory extends Factory
{


    protected $model = PlanUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'user_id' => User::factory(),
            'assigned_at' => now(),
            'active' => true,
        ];
    }
}
