<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanDay>
 */
class PlanDayFactory extends Factory
{

    protected $model = PlanDay::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'week_number' => $this->faker->numberBetween(1, 4),
            'day_number' => $this->faker->numberBetween(1, 7),
            'description' => $this->faker->sentence(),
        ];
    }
}
