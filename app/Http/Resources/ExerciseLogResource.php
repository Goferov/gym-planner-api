<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'date'        => $this->date,
            'completed'   => (bool)$this->completed,
            'actual_sets' => $this->actual_sets,
            'actual_reps' => $this->actual_reps,
            'weight_used' => $this->weight_used,
            'difficulty'  => $this->difficulty_reported,
            'comment'     => $this->difficulty_comment,
        ];
    }
}
