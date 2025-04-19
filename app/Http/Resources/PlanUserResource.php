<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'plan_user_id' => $this->id,
            'started_at'   => $this->started_at,
            'completed_at' => $this->completed_at,
            'progress'     => $this->progress,
            'plan'         => new PlanResource($this->plan),
        ];
    }
}
