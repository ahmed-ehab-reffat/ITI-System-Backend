<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'name' => $this->name,
            'lab_weight' => $this->lab_weight,
            'exam_weight' => $this->exam_weight,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cohort' => new CohortResource($this->whenLoaded('cohort')),
        ];
    }
}
