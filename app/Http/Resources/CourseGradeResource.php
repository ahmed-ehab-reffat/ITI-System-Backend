<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'course_id'      => $this->course_id,
            'student_id'     => $this->student_id,
            'exam_raw_score' => $this->exam_raw_score,
            'exam_raw_max'   => $this->exam_raw_max,
            'computed_score' => $this->computed_score,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'student'        => new UserResource($this->whenLoaded('student')),
            'course'         => new CourseResource($this->whenLoaded('course')),
            'overrides'      => GradeOverrideResource::collection($this->whenLoaded('overrides')),
        ];
    }
}
