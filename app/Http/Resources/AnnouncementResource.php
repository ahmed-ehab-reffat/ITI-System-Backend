<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
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
            'title'      => $this->title,
            'body'       => $this->body,
            'author'     => $this->whenLoaded('author', function () {
                return [
                    'id'   => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
            'cohort_id'  => $this->cohort_id,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
