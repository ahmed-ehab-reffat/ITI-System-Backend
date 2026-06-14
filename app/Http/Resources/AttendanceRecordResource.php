<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'session_id' => $this->session_id,
            'student'    => $this->whenLoaded('student', fn () => [
                'id'   => $this->student->id,
                'name' => $this->student->name,
            ]),
            'type' => $this->session->engagement->type ?? null,
            'arrived_at' => $this->arrived_at,
            'left_at'    => $this->left_at,
            'status'     => $this->status,
            'session'    => $this->whenLoaded('session', fn () => [
                'id'           => $this->session->id,
                'session_date' => $this->session->session_date,
                'engagement'   => $this->session->relationLoaded('engagement') ? [
                    'id'      => $this->session->engagement->id,
                    'type'    => $this->session->engagement->type,
                    'cohort'  => $this->session->engagement->relationLoaded('cohort') ? [
                        'id'   => $this->session->engagement->cohort->id,
                        'name' => $this->session->engagement->cohort->name,
                    ] : null,
                ] : null,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}