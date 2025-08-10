<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDayUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_user_id', 'plan_day_id', 'scheduled_date',
        'status', 'completed_at',
    ];
    protected $table = 'plan_day_user';
    public function planUser()  { return $this->belongsTo(PlanUser::class); }
    public function planDay()   { return $this->belongsTo(PlanDay::class);  }
    public function exercises() { return $this->planDay->exercises(); }
}
