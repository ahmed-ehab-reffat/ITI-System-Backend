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
            'author_id'  => $this->whenLoaded('author', fn() => $this->author->id),
            'author_name'=> $this->whenLoaded('author', fn() => $this->author->name),
            'author_role'=> $this->whenLoaded('author', fn() => $this->author->role),
            'cohort_id'  => $this->cohort_id,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
