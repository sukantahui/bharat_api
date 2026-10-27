<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleOldGoldExchangeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'itemDescription'      => $this->item_description,
            'item_description'     => $this->item_description,
            'description'          => $this->item_description,
            'grossWeight'          => (float) $this->gross_weight,
            'gross_weight'         => (float) $this->gross_weight,
            'dustStoneWeight'      => (float) $this->dust_stone_weight,
            'dust_stone_weight'    => (float) $this->dust_stone_weight,
            'netWeight'            => (float) $this->net_weight,
            'net_weight'           => (float) $this->net_weight,
            'purityPercentage'     => (float) $this->purity_percentage,
            'purity_percentage'    => (float) $this->purity_percentage,
            'purity_percent'       => (float) $this->purity_percentage,
            'fineGoldWeight'       => (float) $this->fine_gold_weight,
            'fine_gold_weight'     => (float) $this->fine_gold_weight,
            'buyingRatePerGram'    => (float) $this->buying_rate_per_gram,
            'buying_rate_per_gram' => (float) $this->buying_rate_per_gram,
            'rate_per_gram'        => (float) $this->buying_rate_per_gram,
            'totalExchangeValue'   => (float) $this->total_exchange_value,
            'total_exchange_value' => (float) $this->total_exchange_value,
            'exchange_value'       => (float) $this->total_exchange_value,
            'remarks'              => $this->remarks,
        ];
    }
}