<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanDayExerciseLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $log = $this->logs->first();

        return [
            'id'         => $this->id, // ID plan_day_exercise
            'exercise'   => $this->exercise->name,
            'sets'       => $this->sets,
            'reps'       => $this->reps,
            'rest_time'  => $this->rest_time,
            'tempo'      => $this->tempo,
            'notes'      => $this->notes,

            'log_id'     => $log?->id,
            'completed'  => $log?->completed ?? false,
            'difficulty' => $log?->difficulty_reported,
            'comment'    => $log?->difficulty_comment,
        ];
    }
}
