<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'entryNumber'              => $this->entry_number,
            'entryDate'                => $this->entry_date?->format('Y-m-d'),
            'entryType'                => $this->entry_type,
            'artisanId'                => $this->artisan_id,
            'artisan'                  => new PartyResource($this->whenLoaded('artisan')),
            'totalItemsCount'          => $this->total_items_count,
            'totalGrossWeight'         => (float) $this->total_gross_weight,
            'totalStoneWeight'         => (float) $this->total_stone_weight,
            'totalNetWeight'           => (float) $this->total_net_weight,
            'totalFineGoldWeight'      => (float) $this->total_fine_gold_weight,
            'totalEstimatedValuation'  => (float) $this->total_estimated_valuation,
            'remarks'                  => $this->remarks,
            'items'                    => StockEntryItemResource::collection($this->whenLoaded('items')),
            'createdAt'                => $this->created_at?->toIso8601String(),
        ];
    }
}
