<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\User $resource
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->resource->id,
            'name'              => $this->resource->name,
            'email'             => $this->resource->email,
            'role'              => $this->resource->role,
            'compensation_type' => $this->resource->compensation_type,
            'fixed_salary'      => $this->resource->fixed_salary ? (float) $this->resource->fixed_salary : null,
            'hourly_rate'       => $this->resource->hourly_rate ? (float) $this->resource->hourly_rate : null,
            'expires_at'        => $this->resource->expires_at?->format('Y-m-d H:i:s'),
            'created_at'        => $this->resource->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
