<?php

namespace Database\Factories;

use App\Models\ExerciseLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseLog>
 */
class ExerciseLogFactory extends Factory
{

    protected $model = ExerciseLog::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        return [
            'plan_user_id' => null,
            'plan_day_exercise_id' => null,
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'actual_sets' => $this->faker->numberBetween(3, 5),
            'actual_reps' => $this->faker->numberBetween(8, 12),
            'weight_used' => $this->faker->randomFloat(1, 20, 100),
            'notes' => $this->faker->sentence(),
        ];
    }
}
