<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'trainer_id',
        'name',
        'description',
        'duration_weeks',
    ];


    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function clients()
    {
        return $this->belongsToMany(
            User::class,
            'plan_user',
            'plan_id',
            'user_id'
        )
            ->withTimestamps()
            ->withPivot(['assigned_at', 'active']);
    }


    public function planDays()
    {
        return $this->hasMany(PlanDay::class, 'plan_id');
    }
}
