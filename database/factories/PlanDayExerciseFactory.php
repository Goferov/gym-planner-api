<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanDayExercise>
 */
class PlanDayExerciseFactory extends Factory
{

    protected $model = PlanDayExercise::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_day_id' => PlanDay::factory(),
            'exercise_id' => Exercise::factory(),
            'sets' => $this->faker->numberBetween(3, 5),
            'reps' => $this->faker->numberBetween(8, 12),
            'rest_time' => $this->faker->numberBetween(30, 120), // w sekundach
            'tempo' => '3-1-1',
            'notes' => $this->faker->sentence(),
        ];
    }
}
