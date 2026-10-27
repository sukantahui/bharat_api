<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'karatName'         => $this->karat_name,
            'karat_name'        => $this->karat_name,
            'karatCode'         => $this->karat_code,
            'karat_code'        => $this->karat_code,
            'purityPercentage'  => (float) $this->purity_percentage,
            'purity_percentage' => (float) $this->purity_percentage,
            'hallmarkCode'      => $this->hallmark_code,
            'hallmark_code'     => $this->hallmark_code,
            'description'       => $this->description,
            'isActive'          => (bool) $this->is_active,
            'is_active'         => (bool) $this->is_active,
        ];
    }
}