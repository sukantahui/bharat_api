<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'jewelleryItemId'   => $this->jewellery_item_id,
            'categoryId'        => $this->category_id,
            'purityId'          => $this->purity_id,
            'itemName'          => $this->item_name,
            'tagCode'           => $this->tag_code,
            'huid'              => $this->huid,
            'hsnCode'           => $this->hsn_code,
            'grossWeight'       => (float) $this->gross_weight,
            'stoneWeight'       => (float) $this->stone_weight,
            'netWeight'         => (float) $this->net_weight,
            'fineGoldWeight'    => (float) $this->fine_gold_weight,
            'goldRatePerGram'   => (float) $this->gold_rate_per_gram,
            'goldAmount'        => (float) $this->gold_amount,
            'makingChargeType'  => $this->making_charge_type,
            'makingChargeRate'  => (float) $this->making_charge_rate,
            'makingCharges'     => (float) $this->making_charges,
            'stoneCharges'      => (float) $this->stone_charges,
            'hallmarkingCharge' => (float) $this->hallmarking_charge,
            'discountAmount'    => (float) $this->discount_amount,
            'taxableAmount'     => (float) $this->taxable_amount,
            'gstRate'           => (float) $this->gst_rate,
            'cgstAmount'        => (float) $this->cgst_amount,
            'sgstAmount'        => (float) $this->sgst_amount,
            'igstAmount'        => (float) $this->igst_amount,
            'totalAmount'       => (float) $this->total_amount,
        ];
    }
}
