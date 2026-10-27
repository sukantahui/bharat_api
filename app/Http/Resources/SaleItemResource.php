<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'jewelleryItemId'       => $this->jewellery_item_id,
            'jewellery_item_id'     => $this->jewellery_item_id,
            'categoryId'            => $this->category_id,
            'category_id'           => $this->category_id,
            'purityId'              => $this->purity_id,
            'purity_id'             => $this->purity_id,
            'purity'                => new PurityResource($this->whenLoaded('purity')),
            'itemName'              => $this->item_name,
            'item_name'             => $this->item_name,
            'tagCode'               => $this->tag_code,
            'tag_code'              => $this->tag_code,
            'huid'                  => $this->huid,
            'hsnCode'               => $this->hsn_code,
            'hsn_code'              => $this->hsn_code,
            'grossWeight'           => (float) $this->gross_weight,
            'gross_weight'          => (float) $this->gross_weight,
            'stoneWeight'           => (float) $this->stone_weight,
            'stone_weight'          => (float) $this->stone_weight,
            'netWeight'             => (float) $this->net_weight,
            'net_weight'            => (float) $this->net_weight,
            'fineGoldWeight'        => (float) $this->fine_gold_weight,
            'fine_gold_weight'      => (float) $this->fine_gold_weight,
            'goldRatePerGram'       => (float) $this->gold_rate_per_gram,
            'gold_rate_per_gram'    => (float) $this->gold_rate_per_gram,
            'rate_per_gram'         => (float) $this->gold_rate_per_gram,
            'goldAmount'            => (float) $this->gold_amount,
            'gold_amount'           => (float) $this->gold_amount,
            'makingChargeType'      => $this->making_charge_type,
            'making_charge_type'    => $this->making_charge_type,
            'makingChargeRate'      => (float) $this->making_charge_rate,
            'making_charge_rate'    => (float) $this->making_charge_rate,
            'makingCharges'         => (float) $this->making_charges,
            'making_charges'        => (float) $this->making_charges,
            'making_charges_amount' => (float) $this->making_charges,
            'stoneCharges'          => (float) $this->stone_charges,
            'stone_charges'         => (float) $this->stone_charges,
            'hallmarkingCharge'     => (float) $this->hallmarking_charge,
            'hallmarking_charge'    => (float) $this->hallmarking_charge,
            'discountAmount'        => (float) $this->discount_amount,
            'discount_amount'       => (float) $this->discount_amount,
            'taxableAmount'         => (float) $this->taxable_amount,
            'taxable_amount'        => (float) $this->taxable_amount,
            'gstRate'               => (float) $this->gst_rate,
            'gst_rate'              => (float) $this->gst_rate,
            'cgstAmount'            => (float) $this->cgst_amount,
            'cgst_amount'           => (float) $this->cgst_amount,
            'sgstAmount'            => (float) $this->sgst_amount,
            'sgst_amount'           => (float) $this->sgst_amount,
            'igstAmount'            => (float) $this->igst_amount,
            'igst_amount'           => (float) $this->igst_amount,
            'totalAmount'           => (float) $this->total_amount,
            'total_amount'          => (float) $this->total_amount,
        ];
    }
}