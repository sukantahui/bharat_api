<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JewelleryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'tagCode'                => $this->tag_code,
            'itemName'               => $this->item_name,
            'categoryId'             => $this->category_id,
            'category'               => new JewelleryCategoryResource($this->whenLoaded('category')),
            'purityId'               => $this->purity_id,
            'purity'                 => new PurityResource($this->whenLoaded('purity')),
            'hsnCode'                => $this->hsn_code,
            'grossWeight'            => (float) $this->gross_weight,
            'stoneWeight'            => (float) $this->stone_weight,
            'netGoldWeight'          => (float) $this->net_gold_weight,
            'fineGoldWeight'         => (float) $this->fine_gold_weight,
            'stoneValue'             => (float) $this->stone_value,
            'makingChargeType'       => $this->making_charge_type,
            'makingChargeRate'       => (float) $this->making_charge_rate,
            'purchaseCost'           => (float) $this->purchase_cost,
            'sellingMinRatePerGram'  => $this->selling_min_rate_per_gram ? (float) $this->selling_min_rate_per_gram : null,
            'huid'                   => $this->huid,
            'sourceType'             => $this->source_type,
            'sourceReferenceId'      => $this->source_reference_id,
            'status'                 => $this->status,
            'soldDate'               => $this->sold_date?->format('Y-m-d'),
            'notes'                  => $this->notes,
        ];
    }
}
