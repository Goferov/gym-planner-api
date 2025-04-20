<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignedPlanResource;
use App\Http\Resources\ExerciseLogResource;
use App\Http\Resources\PlanDayExerciseLogResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\PlanUserHistoryResource;
use App\Http\Resources\PlanUserResource;
use App\Models\ExerciseLog;
use App\Models\PlanDay;
use App\Models\PlanUser;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PlanUserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'trainer' && $request->filled('user_id')) {
            $assigned = PlanUser::where('user_id', $request->input('user_id'))
                ->with('plan')
                ->get();
        } else {
            $assigned = $user->assignedPlans()->with('plan')->get();
        }

        return AssignedPlanResource::collection($assigned);
    }


    public function start(PlanUser $planUser)
    {
        $this->authorize('start', $planUser);

        $planUser->update([
            'started_at' => now(),
            'active'     => true,
        ]);

        return response()->json(['message'=>'Plan started']);
    }

    public function show(PlanUser $planUser)
    {
        $this->authorize('view', $planUser);

        $planUser->tryAutoComplete();

        $planUser->load([
            'plan.planDays.exercises.logs' => fn($q)=>$q->where('plan_user_id',$planUser->id),
            'plan.planDays.exercises.exercise',
            'plan.clients',
        ]);

        return new PlanUserResource($planUser);
    }


    public function showDay(PlanUser $planUser, Request $request)
    {
        $this->authorize('view', $planUser);

        $today = Carbon::parse(
            $request->input('date', Carbon::today()->toDateString())
        )->startOfDay();

        ['model'=>$dayModel] = $this->resolvePlanDay($planUser,$today->toDateString());

        $pending = $this->getPendingDays($planUser, $today);

        if ($dayModel) {
            $dayModel->load([
                'exercises.logs' => fn($q)=>$q->where('plan_user_id',$planUser->id),
                'exercises.exercise'
            ]);

            return response()->json([
                'date'          => $today->toDateString(),
                'week'          => $dayModel->week_number,
                'day'           => $dayModel->day_number,
                'exercises'     => PlanDayExerciseLogResource::collection($dayModel->exercises),
                'pending_days'  => $pending,
                'rest'          => false,
            ]);
        }

        ['model'=>$nextDay,'date'=>$nextDate] =
            $this->resolvePlanDay($planUser,$today->toDateString(),true);

        return response()->json([
            'rest'               => true,
            'message'            => $nextDay
                ? 'No training today - next:'
                : 'The plan does not include more training.',
            'next_training_date' => $nextDate?->toDateString(),
            'next_week_number'   => $nextDay?->week_number,
            'next_day_number'    => $nextDay?->day_number,
            'pending_days'       => $pending,
        ]);
    }



    public function startDay(PlanUser $planUser, Request $request)
    {
        $this->authorize('view', $planUser);

        $date     = $request->input('date', Carbon::today()->toDateString());
        $dayModel = $this->resolvePlanDay($planUser, $date);

        if (!$dayModel) {
            return response()->json(['message'=>'No training scheduled for this date'], 404);
        }

        foreach ($dayModel->exercises as $pde) {
            ExerciseLog::firstOrCreate([
                'plan_user_id'         => $planUser->id,
                'plan_day_exercise_id' => $pde->id,
                'date'                 => $date,
            ]);
        }

        return $this->showDay($planUser, $request);
    }

    public function summary(PlanUser $planUser, Request $request)
    {
        $this->authorize('view', $planUser);

        $date     = $request->input('date', Carbon::today()->toDateString());
        $dayModel = $this->resolvePlanDay($planUser, $date);

        if (!$dayModel) {
            return response()->json(['message'=>'No training scheduled'], 404);
        }

        $total   = $dayModel->exercises->count();
        $done    = ExerciseLog::where('plan_user_id',$planUser->id)
            ->whereDate('date',$date)
            ->where('completed',true)->count();

        $summary = [
            'date'   => $date,
            'total'  => $total,
            'done'   => $done,
            'progress'=> $total ? round($done*100/$total) : 0,
            'all_completed' => $total && $total === $done,
        ];


        return response()->json($summary);
    }

    public function history(PlanUser $planUser)
    {
        $this->authorize('view', $planUser);

        $planUser->load([
            'plan.planDays.exercises.logs' => fn($q) =>
            $q->where('plan_user_id', $planUser->id),
            'plan.planDays.exercises.exercise'
        ]);

        return new PlanUserHistoryResource($planUser);
    }

    /**
     * @return array{model: ?PlanDay, date: ?Carbon}  // model = PlanDay lub null, date = data w kalendarzu
     */
    private function resolvePlanDay(PlanUser $pu, string $date, bool $skipToNext = false): array
    {
        if (!$pu->started_at) {
            return ['model' => null, 'date' => null];
        }

        $start      = $pu->started_at->copy()->startOfDay();
        $carbonDate = Carbon::parse($date)->startOfDay();
        $offsetDays = $carbonDate->diffInDays($start);

        $weekN = intdiv($offsetDays, 7) + 1;
        $dayN  = $offsetDays % 7 + 1;

        $weekDays = $pu->plan->planDays
            ->where('week_number', $weekN)
            ->sortBy('day_number')
            ->values();

        $exact = $weekDays->firstWhere('day_number', $dayN);
        if ($exact) {
            return ['model' => $exact, 'date' => $carbonDate];
        }

        if ($skipToNext) {
            $nextDayModel = $weekDays->first(fn($d) => $d->day_number > $dayN);

            if (!$nextDayModel) {
                $nextDayModel = $pu->plan->planDays
                    ->where('week_number', '>', $weekN)
                    ->sortBy(['week_number', 'day_number'])
                    ->first();
            }

            if ($nextDayModel) {
                $daysOffset = ($nextDayModel->week_number - 1) * 7 + ($nextDayModel->day_number - 1);
                $nextDate   = $start->copy()->addDays($daysOffset);
                return ['model' => $nextDayModel, 'date' => $nextDate];
            }
        }

        return ['model' => null, 'date' => null];
    }

    private function getPendingDays(PlanUser $pu, Carbon $today): array
    {
        $pending = [];

        foreach ($pu->plan->planDays as $day) {
            $offsetDays = ($day->week_number-1)*7 + ($day->day_number-1);
            $scheduled  = $pu->started_at?->copy()->addDays($offsetDays)->startOfDay();

            if (!$scheduled || $scheduled->gte($today)) {
                continue;
            }

            $total = $day->exercises->count();
            $done  = $day->exercises->flatMap->logs
                ->where('plan_user_id',$pu->id)
                ->where('completed',true)->count();

            if ($total === 0 || $done === $total) {
                continue;
            }

            $pending[] = [
                'scheduled_date' => $scheduled->toDateString(),
                'week'           => $day->week_number,
                'day'            => $day->day_number,
                'total'          => $total,
                'done'           => $done,
                'progress'       => round($done*100/$total),
            ];
        }

        // posortuj rosnąco po dacie
        usort($pending, fn($a,$b)=>strcmp($a['scheduled_date'],$b['scheduled_date']));
        return $pending;
    }


}
