<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'session_date' => $this->whenLoaded('session', fn() => $this->session->session_date),
            'hours'        => $this->hours,
            'person_type'  => $this->person_type,
            'rate'         => $this->whenLoaded('user', fn() => $this->user->hourly_rate),
            'amount_due'   => $this->person_type === 'external' && $this->relationLoaded('user')
                ? $this->hours * $this->user->hourly_rate
                : null,
        ];
    }
}
