<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{

    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', Exercise::class);

        $user = Auth::user();

        $query = Exercise::with('muscleGroups');

        $query->where(function ($q) use ($user) {
            $q->where('user_id', 0);
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }
        });

        if ($request->has('muscle_group_id')) {
            $muscleGroupId = $request->input('muscle_group_id');
            $query->whereHas('muscleGroups', function ($subQ) use ($muscleGroupId) {
                $subQ->where('muscle_group_id', $muscleGroupId);
            });
        }

        $exercises = $query->get();

        return ExerciseResource::collection($exercises);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Exercise::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'muscle_group_ids' => 'array',
            'muscle_group_ids.*' => 'integer|exists:muscle_groups,id',
        ]);

        $user = Auth::user();
        $exercise = Exercise::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'video_url' => $request->input('video_url'),
            'user_id' => $user->id,
        ]);

        if ($request->has('muscle_group_ids')) {
            $exercise->muscleGroups()->sync($request->input('muscle_group_ids'));
        }

        return new ExerciseResource($exercise->load('muscleGroups'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise)
    {
        $this->authorize('view', $exercise);

        return new ExerciseResource($exercise->load('muscleGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'muscle_group_ids' => 'array',
            'muscle_group_ids.*' => 'integer|exists:muscle_groups,id',
        ]);

        $exercise->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'video_url' => $request->input('video_url'),
        ]);

        $exercise->muscleGroups()->sync(
            $request->input('muscle_group_ids', [])
        );

        return new ExerciseResource($exercise->load('muscleGroups'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {

        $this->authorize('delete', $exercise);

        $exercise->delete();
        return response(status: 204);
    }
}
