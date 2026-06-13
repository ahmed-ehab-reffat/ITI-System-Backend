<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CohortResource extends JsonResource
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
            'track_id' => $this->track_id,
            'name' => $this->name,
            'status' => $this->status,
            'starts_at' => $this->starts_at->toDateString(),
            'ends_at' => $this->ends_at->toDateString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'track' => new TrackResource($this->whenLoaded('track')),
            'track_admins' => UserResource::collection($this->whenLoaded('trackAdmins')),
        ];
    }
}
