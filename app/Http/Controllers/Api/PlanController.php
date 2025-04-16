<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Http\Controllers\Controller;
use App\Models\PlanDay;
use App\Models\PlanDayExercise;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Plan::class);

        $trainer = auth()->user();
        $plans = Plan::with('clients')
            ->where('trainer_id', $trainer->id)
            ->get();
        return PlanResource::collection($plans);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Plan::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_weeks' => 'nullable|integer',
            'plan_days' => 'array',
            'plan_days.*.week_number' => 'required|integer',
            'plan_days.*.day_number' => 'required|integer',
            'plan_days.*.description' => 'nullable|string',
            'plan_days.*.exercises' => 'array',
            'plan_days.*.exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'plan_days.*.exercises.*.sets' => 'integer',
            'plan_days.*.exercises.*.reps' => 'integer',
            'plan_days.*.exercises.*.rest_time' => 'integer',
            'plan_days.*.exercises.*.tempo' => 'string|nullable',
            'plan_days.*.exercises.*.notes' => 'string|nullable',
        ]);

        $trainer = auth()->user();

        $plan = Plan::create([
            'trainer_id' => $trainer->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'duration_weeks' => $request->input('duration_weeks'),
        ]);

        $planDays = $request->input('plan_days', []);
        foreach ($planDays as $dayData) {
            $planDay = PlanDay::create([
                'plan_id' => $plan->id,
                'week_number' => $dayData['week_number'],
                'day_number' => $dayData['day_number'],
                'description' => $dayData['description'] ?? null,
            ]);

            if (!empty($dayData['exercises'])) {
                foreach ($dayData['exercises'] as $ex) {
                    PlanDayExercise::create([
                        'plan_day_id' => $planDay->id,
                        'exercise_id' => $ex['exercise_id'],
                        'sets' => $ex['sets'] ?? null,
                        'reps' => $ex['reps'] ?? null,
                        'rest_time' => $ex['rest_time'] ?? null,
                        'tempo' => $ex['tempo'] ?? null,
                        'notes' => $ex['notes'] ?? null,
                    ]);
                }
            }
        }

        $plan->load('planDays.exercises');
        return new PlanResource($plan);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        $this->authorize('view', $plan);

        $plan->load('planDays.exercises');
        $plan->load('clients');
        return new PlanResource($plan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_weeks' => 'nullable|integer',
            'plan_days' => 'array|required',
            'plan_days.*.week_number' => 'required|integer',
            'plan_days.*.day_number' => 'required|integer',
            'plan_days.*.description' => 'nullable|string',
            'plan_days.*.exercises' => 'array|required',
            'plan_days.*.exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'plan_days.*.exercises.*.sets' => 'integer|nullable',
            'plan_days.*.exercises.*.reps' => 'integer|nullable',
            'plan_days.*.exercises.*.rest_time' => 'integer|nullable',
            'plan_days.*.exercises.*.tempo' => 'string|nullable',
            'plan_days.*.exercises.*.notes' => 'string|nullable',
        ]);


        $plan->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'duration_weeks' => $request->input('duration_weeks'),
        ]);

        $plan->planDays->each(function($day) {
            $day->exercises()->delete();
        });

        $plan->planDays()->delete();

        $planDaysData = $request->input('plan_days', []);
        foreach ($planDaysData as $dayData) {
            $planDay = $plan->planDays()->create([
                'week_number' => $dayData['week_number'],
                'day_number' => $dayData['day_number'],
                'description' => $dayData['description'] ?? null,
            ]);

            foreach ($dayData['exercises'] as $ex) {
                $planDay->exercises()->create([
                    'exercise_id' => $ex['exercise_id'],
                    'sets' => $ex['sets'] ?? null,
                    'reps' => $ex['reps'] ?? null,
                    'rest_time' => $ex['rest_time'] ?? null,
                    'tempo' => $ex['tempo'] ?? null,
                    'notes' => $ex['notes'] ?? null,
                ]);
            }
        }

        $plan->load('planDays.exercises');
        return new PlanResource($plan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {

        $this->authorize('delete', $plan);

        $plan->planDays->each(function($day) {
            $day->exercises()->delete();
        });
        $plan->planDays()->delete();
        $plan->delete();
        return response(status: 204);
    }

    public function assignPlan(Request $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $trainer = auth()->user();
        $userIds = $request->input('user_ids');

        $attachData = [];

        foreach ($userIds as $id) {
            if ($id == $trainer->id) {
                return response()->json([
                    'message' => 'Cannot assign plan to yourself.'
                ], 400);
            }

            $user = User::find($id);
            if ($user->role !== 'user' || $user->trainer_id !== $trainer->id) {
                return response()->json([
                    'message' => 'User ' . $id . ' is not your client.'
                ], 403);
            }

            $attachData[$id] = [
                'assigned_at' => now(),
                'active' => true,
            ];
        }

        $plan->clients()->syncWithoutDetaching($attachData);

        return response()->json([
            'message' => 'Plan assigned to users successfully'
        ], 201);
    }


    public function unassignPlan(Request $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $trainer = auth()->user();
        $userIds = $request->input('user_ids');  // np. [2,5,7]

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user->trainer_id !== $trainer->id) {
                return response()->json([
                    'message' => "User $userId is not your client."
                ], 403);
            }

            $plan->clients()->updateExistingPivot($userId, [
                'active' => false
            ]);
        }

        return response()->json([
            'message' => 'Plan set to inactive for the specified user(s).'
        ], 200);
    }


}
