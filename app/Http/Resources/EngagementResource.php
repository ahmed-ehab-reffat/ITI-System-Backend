<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementResource extends JsonResource
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
            'cohort_id' => $this->cohort_id,
            'instructor_id' => $this->instructor_id,
            'lab_group_id' => $this->lab_group_id,
            'type' => $this->type,
            'starts_at' => $this->starts_at->toDateString(),
            'ends_at' => $this->ends_at->toDateString(),
            'hours_per_session' => $this->hours_per_session,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'lab_group' => new LabGroupResource($this->whenLoaded('labGroup')),
        ];
    }
}
