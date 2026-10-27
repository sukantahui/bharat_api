<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'jewelleryItemId'  => $this->jewellery_item_id,
            'categoryId'       => $this->category_id,
            'purityId'         => $this->purity_id,
            'itemDescription'  => $this->item_description,
            'hsnCode'          => $this->hsn_code,
            'grossWeight'      => (float) $this->gross_weight,
            'stoneWeight'      => (float) $this->stone_weight,
            'netWeight'        => (float) $this->net_weight,
            'purityPercentage' => (float) $this->purity_percentage,
            'fineGoldWeight'   => (float) $this->fine_gold_weight,
            'ratePerGram'      => (float) $this->rate_per_gram,
            'goldAmount'       => (float) $this->gold_amount,
            'makingCharges'    => (float) $this->making_charges,
            'stoneCharges'     => (float) $this->stone_charges,
            'taxableAmount'    => (float) $this->taxable_amount,
            'gstRate'          => (float) $this->gst_rate,
            'cgstAmount'       => (float) $this->cgst_amount,
            'sgstAmount'       => (float) $this->sgst_amount,
            'igstAmount'       => (float) $this->igst_amount,
            'totalAmount'      => (float) $this->total_amount,
        ];
    }
}
