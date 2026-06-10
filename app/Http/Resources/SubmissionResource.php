<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $finalScore = $this->raw_score !== null
            ? max(0, round((float) $this->raw_score - (float) $this->late_penalty, 2))
            : null;

        return [
            'id'           => $this->id,
            'session_id'   => $this->session_id,
            'student_id'   => $this->student_id,
            'url'          => $this->url,
            'file_path'    => $this->file_path,
            'submitted_at' => $this->submitted_at,
            'raw_score'    => $this->raw_score,
            'late_penalty' => $this->late_penalty,
            'final_score'  => $finalScore,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'student'      => new UserResource($this->whenLoaded('student')),
            'session'      => new SessionResource($this->whenLoaded('session')),
        ];
    }
}
