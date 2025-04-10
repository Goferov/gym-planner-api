<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    use HasFactory;

    protected $table = 'exercise_logs';

    protected $fillable = [
        'plan_user_id',
        'plan_day_exercise_id',
        'date',
        'actual_sets',
        'actual_reps',
        'weight_used',
        'notes',
        'completed',
        'difficulty_reported',
        'difficulty_comment',
    ];


    public function planUser()
    {
        return $this->belongsTo(PlanUser::class, 'plan_user_id');
    }


    public function planDayExercise()
    {
        return $this->belongsTo(PlanDayExercise::class, 'plan_day_exercise_id');
    }
}
