<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanUser extends Model
{
    use HasFactory;

    protected $table = 'plan_user';

    protected $fillable = [
        'plan_id',
        'user_id',
        'assigned_at',
        'started_at',
        'completed_at',
        'active',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'assigned_at'  => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
    protected $appends = ['progress'];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scheduledDays()
    {
        return $this->hasMany(PlanDayUser::class);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->plan->planDays->flatMap->exercises->count();
        $done  = $this->plan->planDays
            ->flatMap->exercises
            ->flatMap->logs
            ->where('plan_user_id',$this->id)
            ->where('completed',true)->count();
        return $total ? round($done*100/$total) : 0;
    }

    public function tryAutoComplete(): void
    {
        if ($this->completed_at) return;
        $durationDays = $this->plan->duration_weeks * 7;
        $endDate      = $this->started_at?->copy()->addDays($durationDays);

        $finishedAll  = $this->progress === 100;
        $timePassed   = $this->started_at && now()->gte($endDate);

        if ($finishedAll || $timePassed) {
            $this->update(['completed_at'=>now(),'active'=>false]);
        }
    }
}
