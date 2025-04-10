<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseLogController extends Controller
{
    use AuthorizesRequests;

    public function markComplete(Request $request, ExerciseLog $exerciseLog)
    {
        $this->authorize('markComplete', $exerciseLog);

        $request->validate([
            'completed' => 'boolean',
        ]);

        $exerciseLog->completed = $request->input('completed', true);
        $exerciseLog->save();

        return response()->json([
            'message' => 'Exercise marked as complete',
            'exercise_log' => $exerciseLog
        ]);
    }

    public function reportDifficulty(Request $request, ExerciseLog $exerciseLog)
    {
        $this->authorize('reportDifficulty', $exerciseLog);

        $request->validate([
            'difficulty_reported' => 'required|integer|min:1|max:5',
            'difficulty_comment' => 'nullable|string',
        ]);

        $exerciseLog->difficulty_reported = $request->input('difficulty_reported');
        $exerciseLog->difficulty_comment = $request->input('difficulty_comment');
        $exerciseLog->save();

        return response()->json([
            'message' => 'Difficulty reported successfully',
            'exercise_log' => $exerciseLog
        ]);
    }
}
