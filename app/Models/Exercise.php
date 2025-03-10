<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $table = 'exercises';

    protected $fillable = [
        'name',
        'description',
        'video_url',
        'user_id',
    ];

    public function planDayExercises(): HasMany
    {
        return $this->hasMany(PlanDayExercise::class, 'exercise_id');
    }

    public function muscleGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            MuscleGroup::class,
            'exercise_muscle_group',
            'exercise_id',
            'muscle_group_id'
        )
            ->withTimestamps();
    }
}
