<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeOverrideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'course_grade_id' => $this->course_grade_id,
            'overridden_by'   => $this->overridden_by,
            'original_value'  => $this->original_value,
            'new_value'       => $this->new_value,
            'reason'          => $this->reason,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'overridden_by_user' => new UserResource($this->whenLoaded('overriddenBy')),
        ];
    }
}
