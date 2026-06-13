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
            'id'         => $this->id,
            'date'       => $this->whenLoaded('session', fn() => \Carbon\Carbon::parse($this->session->session_date)->format('M d, Y')),
            'engagement' => $this->whenLoaded('session', fn() => ucfirst($this->session->engagement->type) . ' - ' . ($this->session->engagement->labGroup->name ?? 'Lecture')),
            'hours'      => $this->hours,
            'rate'       => $this->whenLoaded('user', fn() => $this->user->hourly_rate ?? 0),
            'amount'     => $this->person_type === 'external' && $this->relationLoaded('user')
                ? $this->hours * ($this->user->hourly_rate ?? 0)
                : 0,
        ];
    }
}
