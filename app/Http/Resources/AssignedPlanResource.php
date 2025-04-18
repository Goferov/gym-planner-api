<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'plan_id'       => $this->plan->id,
            'plan_name'     => $this->plan->name,
            'description'   => $this->plan->description,
            'duration_weeks'=> $this->plan->duration_weeks,
            'assigned_at'   => $this->assigned_at,
            'started_at'    => $this->started_at,
            'completed_at'  => $this->completed_at,
            'active'        => (bool)$this->active,
            'progress'      => $this->progress,
        ];
    }
}
