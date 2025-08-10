<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MuscleGroup;
use App\Models\Exercise;
use App\Models\Plan;
use App\Models\PlanUser;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use App\Models\ExerciseLog;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
// database/seeders/DatabaseSeeder.php
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }

}
