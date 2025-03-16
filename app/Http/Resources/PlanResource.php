<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_weeks' => $this->duration_weeks,

            'trainer_id' => $this->trainer_id,

            // planDays -> only if loaded
            'plan_days' => $this->whenLoaded('planDays', function () {
                return $this->planDays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'week_number' => $day->week_number,
                        'day_number' => $day->day_number,
                        'description' => $day->description,
                        'exercises' => $day->exercises->map(function ($pde) {
                            return [
                                'id' => $pde->id,
                                'exercise_id' => $pde->exercise_id,
                                'sets' => $pde->sets,
                                'reps' => $pde->reps,
                                'rest_time' => $pde->rest_time,
                                'tempo' => $pde->tempo,
                                'notes' => $pde->notes,
                            ];
                        }),
                    ];
                });
            }),

            'clients' => $this->whenLoaded('clients', function () {
                return $this->clients
                    ->filter(function($client) {
                        return $client->pivot->active;
                    })
                    ->map(function ($client) {
                        return [
                            'id'    => $client->id,
                            'name'  => $client->name,
                            'email' => $client->email,
                        ];
                    })
                    ->values();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
