<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseLog;
use App\Models\PlanDayUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseLogController extends Controller
{
    use AuthorizesRequests;

    public function markComplete(Request $request, ExerciseLog $exerciseLog)
    {
        $this->authorize('markComplete', $exerciseLog);

        $request->validate(['completed' => 'boolean']);

        $exerciseLog->completed = $request->input('completed', true);
        $exerciseLog->save();

        $this->tryAutoCompleteDay($exerciseLog);

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
        $exerciseLog->completed = $request->input('completed', true);
        $exerciseLog->save();

        return response()->json([
            'message' => 'Difficulty reported successfully',
            'exercise_log' => $exerciseLog
        ]);
    }

    /* -------- helper: czy domknąć dzień -------- */
    private function tryAutoCompleteDay(ExerciseLog $log): void
    {
        $row = PlanDayUser::where([
            'plan_user_id' =>$log->plan_user_id,
            'plan_day_id'  =>$log->planDayExercise->plan_day_id,
            'scheduled_date'=>$log->date,
        ])->first();

        if (!$row || $row->status !== 'pending') return;

        $total = $row->planDay->exercises->count();
        $done  = $row->planDay->exercises->flatMap->logs
            ->where('plan_user_id',$row->plan_user_id)
            ->where('date',$row->scheduled_date)
            ->where('completed',true)->count();

        if ($total && $done === $total) {
            $row->update(['status'=>'completed','completed_at'=>now()]);
        }
    }

}
