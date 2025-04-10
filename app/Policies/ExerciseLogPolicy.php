<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ExerciseLog;
use App\Models\User;

class ExerciseLogPolicy
{
    public function markComplete(User $user, ExerciseLog $log)
    {
        return $user->id === $log->planUser->user_id;
    }


    public function reportDifficulty(User $user, ExerciseLog $log)
    {
        return $user->id === $log->planUser->user_id;
    }
}
