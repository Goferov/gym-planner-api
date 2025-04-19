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

        $date     = $request->input('date', Carbon::today()->toDateString());
        $dayModel = $this->resolvePlanDay($planUser, $date);

        if (!$dayModel) {
            return response()->json([
                'date'    => $date,
                'rest'    => true,
                'message' => 'Rest day – no training planned'
            ]);
        }

        $dayModel->load(['exercises.logs' => fn($q)=>$q->where('plan_user_id',$planUser->id),
            'exercises.exercise']);

        return response()->json([
            'date'      => $date,
            'week'      => $dayModel->week_number,
            'day'       => $dayModel->day_number,
            'exercises' => PlanDayExerciseLogResource::collection($dayModel->exercises),
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

    private function resolvePlanDay(PlanUser $pu, string $date, bool $skipToNext = false): ?PlanDay
    {
        if (!$pu->started_at) return null;

        $carbonDate = Carbon::parse($date);
        $offsetDays = $carbonDate->diffInDays($pu->started_at);

        $week = intdiv($offsetDays, 7) + 1;
        $dayN = $offsetDays % 7 + 1;

        $weekDays = $pu->plan->planDays
            ->where('week_number', $week)
            ->sortBy('day_number')
            ->values();

        $exact = $weekDays->firstWhere('day_number', $dayN);
        if ($exact) return $exact;

        if ($skipToNext) {
            return $weekDays->first(fn($d) => $d->day_number > $dayN);
        }

        return null;
    }

}
