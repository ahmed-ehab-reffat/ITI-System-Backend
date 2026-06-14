<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExcuseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->student?->name,
            'session_date' => $this->attendanceRecord?->session?->session_date,
            'attendance_record_id' => $this->attendance_record_id,
            'reason' => $this->reason,
            'status' => $this->status,
            'attachment_url' => $this->attachment_path
                ? asset('storage/' . $this->attachment_path)
                : null,

            'reviewer_note' => $this->reviewer_note,
            'reviewed_by' => $this->reviewed_by,
            'reviewer_name' => $this->reviewer?->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => $this->whenLoaded('student'),
            'attendance_record' => $this->whenLoaded('attendanceRecord'),
            'reviewer' => $this->whenLoaded('reviewer'),
        ];
    }
}