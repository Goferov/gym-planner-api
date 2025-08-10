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
    public function run()
    {
//        // 1) Kilku userów:
//        // - 1 admin
//        User::factory()->create([
//            'name' => 'Admin User',
//            'email' => 'admin@example.com',
//            'password' => Hash::make('password'),
//            'role' => 'admin',
//        ]);
////
//        User::factory()->create([
//            'name' => 'Marcin',
//            'email' => 'marcin@innweb.pl',
//            'password' => Hash::make('password'),
//            'role' => 'trainer',
//        ]);

//        // - 2 trenerów
//        $trainers = User::factory(2)->create([
//            'role' => 'trainer',
//        ]);
//
        // - 5 zwykłych użytkowników (domyślnie role = 'user')
        $clients = User::factory(5)->create();

        // 2) Kilka grup mięśniowych
        // Możesz też zrobić to przez MuscleGroupFactory::factory(5)->create()
        $muscleGroupNames = ['Chest', 'Back', 'Legs', 'Shoulders', 'Arms'];
        foreach ($muscleGroupNames as $name) {
            MuscleGroup::factory()->create(['name' => $name]);
        }

        // 3) Tworzymy 10 ćwiczeń i przypinamy do nich losowe grupy mięśni
        $exercises = Exercise::factory(10)->create();

        $allMGs = MuscleGroup::all();
        foreach ($exercises as $exercise) {
            // losowo 1-2 grupy mięśni
            $randomMGs = $allMGs->random(rand(1, 2));
            $exercise->muscleGroups()->attach($randomMGs);
        }
//
//        // 4) Tworzymy np. 3 plany, każdy przypisany do jednego z trenerów
//        $plans = Plan::factory(3)->create()->each(function ($plan) use ($trainers, $clients) {
//            // Nadpisz trainer_id, aby wziąć losowego trenera
//            $plan->trainer_id = $trainers->random()->id;
//            $plan->save();
//
//            // Przypisz losowy podzbiór klientów do planu
//            $someClients = $clients->random(rand(2, 5));
//            foreach ($someClients as $client) {
//                $plan->clients()->attach($client->id, [
//                    'assigned_at' => now(),
//                    'active' => true,
//                ]);
//            }
//
//            // Stwórz 2 dni w każdym planie
//            $planDays = PlanDay::factory(2)->create([
//                'plan_id' => $plan->id
//            ]);
//
//            // W każdym dniu przypisz 3 ćwiczenia
//            $planDays->each(function ($day) {
//                // Każdy PlanDayExercise przypisz do losowego ćwiczenia
//                PlanDayExercise::factory(3)->create([
//                    'plan_day_id' => $day->id,
//                ]);
//            });
//        });
//
//        // (Opcjonalnie) Tworzenie ExerciseLogs dla testu:
//        // Znajdź wszystkie plan_user
//        $allPlanUsers = PlanUser::all();
//        $allPlanDayExercises = PlanDayExercise::all();
//
//        // Dla uproszczenia – do każdego plan_user i plan_day_exercise
//        // utworzymy 1 losowy log, że coś zostało wykonane.
//        foreach ($allPlanUsers as $planUser) {
//            // pobieramy PDE z planu, którego dotyczy planUser
//            // planUser->plan_id -> plan->planDays->exercises
//            // Ewentualnie można najpierw wczytać ->with('plan.planDays.exercises')
//            // ale dla przykładu zrobimy najprościej:
//            $planDays = $planUser->plan->planDays;
//
//            // Zbierz wszystkie PDE z planu:
//            $pdesFromPlan = $planDays->flatMap->exercises; // exercises() w PlanDay zwraca plan_day_exercises
//            if ($pdesFromPlan->count()) {
//                // Losowo 1-2 PDE (dla przykładu)
//                $somePDEs = $pdesFromPlan->random(rand(1, 2));
//                foreach ($somePDEs as $pde) {
//                    ExerciseLog::factory()->create([
//                        'plan_user_id' => $planUser->id,
//                        'plan_day_exercise_id' => $pde->id,
//                    ]);
//                }
//            }
//        }


    }
}
