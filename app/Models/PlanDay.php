<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDay extends Model
{
    use HasFactory;

    protected $table = 'plan_days';

    protected $fillable = [
        'plan_id',
        'week_number',
        'day_number',
        'description',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function exercises()
    {
        return $this->hasMany(PlanDayExercise::class, 'plan_day_id');
    }
}
