<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'purchaseInvoiceNo'    => $this->purchase_invoice_no,
            'purchaseDate'         => $this->purchase_date?->format('Y-m-d'),
            'vendorBillNo'         => $this->vendor_bill_no,
            'vendorBillDate'       => $this->vendor_bill_date?->format('Y-m-d'),
            'partyId'              => $this->party_id,
            'party'                => new PartyResource($this->whenLoaded('party')),
            'purchaseType'         => $this->purchase_type,
            'isGstApplicable'      => (bool) $this->is_gst_applicable,
            'taxType'              => $this->tax_type,
            'totalGrossWeight'     => (float) $this->total_gross_weight,
            'totalNetWeight'       => (float) $this->total_net_weight,
            'totalFineGoldWeight'  => (float) $this->total_fine_gold_weight,
            'totalGoldAmount'      => (float) $this->total_gold_amount,
            'totalMakingCharges'   => (float) $this->total_making_charges,
            'totalStoneCharges'    => (float) $this->total_stone_charges,
            'otherCharges'         => (float) $this->other_charges,
            'discountAmount'       => (float) $this->discount_amount,
            'taxableAmount'        => (float) $this->taxable_amount,
            'cgstRate'             => (float) $this->cgst_rate,
            'cgstAmount'           => (float) $this->cgst_amount,
            'sgstRate'             => (float) $this->sgst_rate,
            'sgstAmount'           => (float) $this->sgst_amount,
            'igstRate'             => (float) $this->igst_rate,
            'igstAmount'           => (float) $this->igst_amount,
            'totalTaxAmount'       => (float) $this->total_tax_amount,
            'roundOff'             => (float) $this->round_off,
            'grandTotal'           => (float) $this->grand_total,
            'paidAmount'           => (float) $this->paid_amount,
            'balanceAmount'        => (float) $this->balance_amount,
            'paymentStatus'        => $this->payment_status,
            'isCancelled'          => (bool) $this->is_cancelled,
            'cancellationReason'   => $this->cancellation_reason,
            'itcEligibility'       => $this->itc_eligibility,
            'notes'                => $this->notes,
            'items'                => PurchaseItemResource::collection($this->whenLoaded('items')),
            'createdAt'            => $this->created_at?->toIso8601String(),
        ];
    }
}
