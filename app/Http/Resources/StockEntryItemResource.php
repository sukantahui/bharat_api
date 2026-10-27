<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockEntryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'jewelleryItemId'      => $this->jewellery_item_id,
            'categoryId'           => $this->category_id,
            'purityId'             => $this->purity_id,
            'itemName'             => $this->item_name,
            'tagCode'              => $this->tag_code,
            'huid'                 => $this->huid,
            'grossWeight'          => (float) $this->gross_weight,
            'stoneWeight'          => (float) $this->stone_weight,
            'netWeight'            => (float) $this->net_weight,
            'fineWeight'           => (float) $this->fine_weight,
            'estimatedRatePerGram' => (float) $this->estimated_rate_per_gram,
            'makingCost'           => (float) $this->making_cost,
            'stoneCost'            => (float) $this->stone_cost,
            'totalCost'            => (float) $this->total_cost,
            'notes'                => $this->notes,
        ];
    }
}
