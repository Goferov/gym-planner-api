<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{

    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trainer_id' => User::factory()->state(['role' => 'trainer']),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'duration_weeks' => $this->faker->numberBetween(4, 12),
        ];
    }
}
