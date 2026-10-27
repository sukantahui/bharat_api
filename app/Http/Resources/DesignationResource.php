<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'designationId' => $this->id,
            'designationName' => $this->designation_name ?? null,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
