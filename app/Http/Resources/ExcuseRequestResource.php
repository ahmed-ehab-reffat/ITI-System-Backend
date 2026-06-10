<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExcuseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'attendance_record_id' => $this->attendance_record_id,
            'student_id'           => $this->student_id,
            'reason'               => $this->reason,
            'attachment_path'      => $this->attachment_path,
            'status'               => $this->status,
            'reviewed_by'          => $this->reviewed_by,
            'reviewer_note'        => $this->reviewer_note,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
            'student'              => new UserResource($this->whenLoaded('student')),
            'attendance_record'    => new AttendanceRecordResource($this->whenLoaded('attendanceRecord')),
            'reviewer'             => new UserResource($this->whenLoaded('reviewer')),
        ];
    }
}
