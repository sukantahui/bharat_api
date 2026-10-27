<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleOldGoldExchangeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'itemDescription'    => $this->item_description,
            'grossWeight'        => (float) $this->gross_weight,
            'dustStoneWeight'    => (float) $this->dust_stone_weight,
            'netWeight'          => (float) $this->net_weight,
            'purityPercentage'   => (float) $this->purity_percentage,
            'fineGoldWeight'     => (float) $this->fine_gold_weight,
            'buyingRatePerGram'  => (float) $this->buying_rate_per_gram,
            'totalExchangeValue' => (float) $this->total_exchange_value,
            'remarks'            => $this->remarks,
        ];
    }
}
