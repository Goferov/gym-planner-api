<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ExerciseResource;
use App\Http\Resources\MuscleGroupResource;
use App\Models\Exercise;
use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
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
            $q->where('user_id', 1);
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
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'video_url'         => 'nullable|url',
            'image'             => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'preferred_media'   => 'nullable|in:image,video',
            'muscle_group_ids'  => 'array',
            'muscle_group_ids.*'=> 'integer|exists:muscle_groups,id',
        ]);


        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('exercises', 'public');
        }

        $user = Auth::user();
        $exercise = Exercise::create([
            'name'            => $request->input('name'),
            'description'     => $request->input('description'),
            'video_url'       => $request->input('video_url'),
            'image_path'      => $imagePath,
            'preferred_media' => $request->input('preferred_media','image'),
            'user_id'         => $user->id,
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
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'video_url'         => 'nullable|url',
            'image'             => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'preferred_media'   => 'nullable|in:image,video',
            'muscle_group_ids'  => 'array',
            'muscle_group_ids.*'=> 'integer|exists:muscle_groups,id',
        ]);


        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('exercises', 'public');
        }


        $data = [
            'name'            => $request->input('name'),
            'description'     => $request->input('description'),
            'video_url'       => $request->input('video_url'),
            'preferred_media' => $request->input('preferred_media','image'),
        ];
        if ($imagePath) $data['image_path'] = $imagePath;

        $exercise->update($data);

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

    public function muscleGroups()
    {
        $muscleGroups = MuscleGroup::all();
        return MuscleGroupResource::collection($muscleGroups);
    }
}
