<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyGoldRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'rateDate'           => $this->rate_date?->format('Y-m-d'),
            'purityId'           => $this->purity_id,
            'purity'             => new PurityResource($this->whenLoaded('purity')),
            'metalType'          => $this->metal_type,
            'sellingRatePerGram' => (float) $this->selling_rate_per_gram,
            'buyingRatePerGram'  => (float) $this->buying_rate_per_gram,
            'effectiveTime'      => $this->effective_time,
            'notes'              => $this->notes,
        ];
    }
}
