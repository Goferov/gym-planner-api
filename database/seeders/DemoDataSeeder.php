<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\Plan;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use App\Models\PlanUser;
use App\Models\User;
use App\Models\ExerciseLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 0) SYSTEM USER (id=1) – dla „domyślnych” ćwiczeń
            $system = User::query()->firstOrCreate(

                [
                    'name'       => 'System',
                    'email'      => 'system@gym.local',
                    'password'   => Hash::make(Str::random(16)),
                    'role'       => 'trainer',
                    'trainer_id' => null,
                ]
            );

            // 1) DEMO TRAINER
            $trainer = User::query()->firstOrCreate(
                ['email' => 'trainer.demo@gym.local'],
                [
                    'name'           => 'Demo Trainer',
                    'password'       => Hash::make('password'),
                    'role'           => 'trainer',
                    'trainer_id'     => null,
                    'last_login_at'  => now()->subDays(1),
                    'notes'          => 'Demo account for screenshots',
                    'address'        => '123 Demo St',
                    'phone'          => '+48 500 000 000',
                ]
            );

            // 2) MUSCLE GROUPS
            $groups = [
                'Chest','Back','Legs','Shoulders','Arms','Core','Glutes','Full Body'
            ];
            foreach ($groups as $g) {
                MuscleGroup::firstOrCreate(['name' => $g]);
            }
            $mgIds = MuscleGroup::pluck('id','name');

            // 3) EXERCISES (część systemowych + część trenera)
            $sysExercises = [
                ['Push-up','Core/Chest',''],
                ['Bodyweight Squat','Legs',''],
                ['Plank','Core',''],
                ['Burpee','Full Body',''],
                ['Lunge','Legs',''],
                ['Mountain Climbers','Core',''],
            ];
            foreach ($sysExercises as [$name,$desc]) {
                $ex = Exercise::firstOrCreate(
                    ['name'=>$name,'user_id'=>$system->id],
                    ['description'=>$desc, 'video_url'=>null]
                );
                // proste losowe przypisanie 1–2 grup
                $ex->muscleGroups()->syncWithoutDetaching(
                    collect($mgIds->values())->shuffle()->take(rand(1,2))->all()
                );
            }

            $trainerExercises = [
                ['Bench Press','Barbell bench',''],
                ['Deadlift','Conventional deadlift',''],
                ['Overhead Press','Standing OHP',''],
                ['Barbell Row','Bent-over row',''],
                ['Romanian Deadlift','Hamstrings focus',''],
            ];
            foreach ($trainerExercises as [$name,$desc]) {
                $ex = Exercise::firstOrCreate(
                    ['name'=>$name,'user_id'=>$trainer->id],
                    ['description'=>$desc,'video_url'=>null]
                );
                $ex->muscleGroups()->syncWithoutDetaching(
                    collect($mgIds->values())->shuffle()->take(2)->all()
                );
            }

            $allExercises = Exercise::pluck('id')->all();

            // 4) CLIENTS (8 szt.)
            $clients = collect();
            for ($i=1; $i<=8; $i++) {
                $c = User::firstOrCreate(
                    ['email' => "client{$i}@gym.local"],
                    [
                        'name'          => "Client {$i}",
                        'password'      => Hash::make('password'),
                        'role'          => 'user',
                        'trainer_id'    => $trainer->id,
                        'last_login_at' => now()->subDays(rand(0,7)),
                        'notes'         => fake()->sentence(),
                        'address'       => fake()->streetAddress(),
                        'phone'         => '+48 500 00 '.str_pad((string)rand(0,999),3,'0',STR_PAD_LEFT),
                    ]
                );
                $clients->push($c);
            }

            // 5) PLANY (2 szt., po 2 tygodnie; trening w tygodniu: dni 1/3/5)
            $plans = collect();
            $plans->push($this->makePlan($trainer, 'Full Body Beginner', 2, [1,3,5], $allExercises));
            $plans->push($this->makePlan($trainer, 'Strength Focus', 2, [2,4,6], $allExercises));

            // 6) PRZYPISANIA PLANÓW (część aktywnych, część ukończonych)
            $today = today();
            $planUsers = collect();

            foreach ($clients as $idx => $client) {
                $plan = $plans[$idx % $plans->count()];
                $assignedAt = $today->copy()->subDays(rand(10,25));
                $startedAt  = $assignedAt->copy()->addDays(rand(0,3));

                $pu = PlanUser::create([
                    'plan_id'      => $plan->id,
                    'user_id'      => $client->id,
                    'assigned_at'  => $assignedAt,
                    'started_at'   => $startedAt,
                    'completed_at' => null,
                    'active'       => true,
                ]);

                $planUsers->push($pu);

                // 6a) Zmaterializuj harmonogram (plan_day_user)
                $this->materializeSchedule($pu);

                // 7) Wygeneruj logi: do wczoraj część completed, część missed + trudności
                $this->generateLogsForPast($pu, $today);
            }

            // 8) Dla 2 klientów „zakończ” plan (ładnie wygląda w statystykach)
            $planUsers->take(2)->each(function(PlanUser $pu) {
                $pu->update([
                    'completed_at' => $pu->started_at?->copy()->addWeeks($pu->plan->duration_weeks),
                    'active'       => false,
                ]);
            });

            $this->command->info('Demo data seeded.');
            $this->command->warn('Trainer login: trainer.demo@gym.local / password');
        });
    }

    private function makePlan(User $trainer, string $name, int $weeks, array $daysInWeek, array $exercisePool): Plan
    {
        $plan = Plan::create([
            'trainer_id'     => $trainer->id,
            'name'           => $name,
            'description'    => 'Autogenerated demo plan',
            'duration_weeks' => $weeks,
        ]);

        for ($w=1; $w <= $weeks; $w++) {
            foreach ($daysInWeek as $d) {
                $day = PlanDay::create([
                    'plan_id'     => $plan->id,
                    'week_number' => $w,
                    'day_number'  => $d,
                    'description' => fake()->sentence(),
                ]);

                // 3–5 ćwiczeń
                foreach (collect($exercisePool)->shuffle()->take(rand(3,5)) as $exId) {
                    PlanDayExercise::create([
                        'plan_day_id' => $day->id,
                        'exercise_id' => $exId,
                        'sets'        => rand(3,5),
                        'reps'        => [8,10,12,15][rand(0,3)],
                        'rest_time'   => [60,90,120][rand(0,2)],
                        'tempo'       => null,
                        'notes'       => null,
                    ]);
                }
            }
        }

        return $plan->fresh(['planDays.exercises']);
    }

    private function materializeSchedule(PlanUser $pu): void
    {
        // Użyj poprawnej nazwy tabeli (u Ciebie to pojedyncze: plan_day_user)
        $table = 'plan_day_user';

        $start = $pu->started_at?->copy()->startOfDay();
        if (!$start) return;

        $days = $pu->plan->planDays()->with('exercises')->get()
            ->sortBy(['week_number','day_number']);

        foreach ($days as $day) {
            $offset = ($day->week_number - 1) * 7 + ($day->day_number - 1);
            $date = $start->copy()->addDays($offset)->toDateString();

            DB::table($table)->updateOrInsert(
                ['plan_user_id' => $pu->id, 'plan_day_id' => $day->id],
                ['scheduled_date' => $date, 'status' => 'pending', 'completed_at' => null, 'created_at'=>now(),'updated_at'=>now()]
            );
        }
    }

    private function generateLogsForPast(PlanUser $pu, Carbon $today): void
    {
        $schedule = DB::table('plan_day_user')
            ->where('plan_user_id', $pu->id)
            ->orderBy('scheduled_date')
            ->get();

        foreach ($schedule as $row) {
            $date = Carbon::parse($row->scheduled_date);
            if ($date->gte($today)) {
                // przyszłość – nie generujemy logów
                continue;
            }

            /** @var PlanDay $planDay */
            $planDay = PlanDay::with('exercises')->find($row->plan_day_id);
            if (!$planDay) continue;

            $total = $planDay->exercises->count();
            if ($total === 0) continue;

            // Szansa na „missed” dzień (15–25%)
            $missedDay = rand(1,100) <= rand(15,25);

            $doneCount = $missedDay ? rand(0, max(0,$total-1)) : rand((int)ceil($total*0.6), $total);

            $completedIds = $planDay->exercises->pluck('id')->shuffle()->take($doneCount)->all();

            foreach ($planDay->exercises as $pde) {
                $log = ExerciseLog::firstOrCreate([
                    'plan_user_id'         => $pu->id,
                    'plan_day_exercise_id' => $pde->id,
                    'date'                 => $date->toDateString(),
                ]);

                $isCompleted = in_array($pde->id, $completedIds, true);

                $log->completed = $isCompleted;
                if (!$isCompleted && rand(0,1)) {
                    $log->difficulty_reported = rand(2,5);
                    $log->difficulty_comment  = fake()->optional(0.6)->sentence();
                }
                $log->save();
            }

            // Zaktualizuj status dnia
            $status = $doneCount === 0 ? 'missed' : ($doneCount === $total ? 'completed' : 'pending');
            DB::table('plan_day_user')
                ->where('plan_user_id',$pu->id)
                ->where('plan_day_id',$planDay->id)
                ->update([
                    'status'       => $status,
                    'completed_at' => $status === 'completed' ? $date : null,
                    'updated_at'   => now(),
                ]);
        }
    }
}
