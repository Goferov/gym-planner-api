<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanUserHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'plan_name'     => $this->plan->name,
            'started_at'    => $this->started_at,
            'completed_at'  => $this->completed_at,
            'progress'      => $this->progress,
            'weeks'         => $this->plan->planDays->groupBy('week_number')->map(function ($days) {
                return $days->map(function ($day) {
                    return [
                        'day_number'  => $day->day_number,
                        'description' => $day->description,
                        'exercises'   => $day->exercises->map(function ($ex) {
                            $log = $ex->logs->first();
                            return [
                                'exercise'   => $ex->exercise->name,
                                'sets'       => $ex->sets,
                                'reps'       => $ex->reps,
                                'completed'  => $log?->completed,
                                'date'       => $log?->date,
                                'difficulty' => $log?->difficulty_reported,
                                'comment'    => $log?->difficulty_comment,
                            ];
                        }),
                    ];
                })->values();
            })->toArray(),
        ];
    }
}
