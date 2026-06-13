<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTagResource extends JsonResource
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
            'tag_type'   => $this->tag_type,
            'tag_value'  => $this->tag_value,
            'note'       => $this->note,
            'tagged_by'  => $this->whenLoaded('tagger', function () {
                return [
                    'id'   => $this->tagger->id,
                    'name' => $this->tagger->name,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
