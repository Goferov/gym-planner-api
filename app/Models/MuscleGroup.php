<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MuscleGroup extends Model
{
    use HasFactory;

    protected $table = 'muscle_groups';

    protected $fillable = [
        'name',
    ];


    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(
            Exercise::class,
            'exercise_muscle_group',
            'muscle_group_id',
            'exercise_id'
        )
            ->withTimestamps();
    }
}
