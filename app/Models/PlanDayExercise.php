<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDayExercise extends Model
{
    use HasFactory;

    protected $table = 'plan_day_exercises';

    protected $fillable = [
        'plan_day_id',
        'exercise_id',
        'sets',
        'reps',
        'rest_time',
        'tempo',
        'notes',
    ];


    public function planDay()
    {
        return $this->belongsTo(PlanDay::class, 'plan_day_id');
    }


    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }


    public function logs()
    {
        return $this->hasMany(ExerciseLog::class, 'plan_day_exercise_id');
    }
}
