<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $invDate = $this->invoice_date?->format('Y-m-d');
        $createdIso = $this->created_at?->toIso8601String();

        return [
            'id'                         => $this->id,
            // Invoice identifiers (both camelCase and snake_case supported)
            'invoiceNumber'              => $this->invoice_number,
            'invoice_number'             => $this->invoice_number,
            'invoiceDate'                => $invDate,
            'invoice_date'               => $invDate,
            // Party / Customer info
            'partyId'                    => $this->party_id,
            'party_id'                   => $this->party_id,
            'party'                      => new PartyResource($this->whenLoaded('party')),
            'customerName'               => $this->customer_name,
            'customer_name'              => $this->customer_name,
            'customerMobile'             => $this->customer_mobile,
            'customer_mobile'            => $this->customer_mobile,
            'customerEmail'              => $this->customer_email,
            'customer_email'             => $this->customer_email,
            'customerAddress'            => $this->customer_address,
            'customer_address'           => $this->customer_address,
            'customerGstin'              => $this->customer_gstin,
            'customer_gstin'             => $this->customer_gstin,
            'customerPan'                => $this->customer_pan,
            'customer_pan'               => $this->customer_pan,
            // Tax & POS locations
            'placeOfSupplyStateId'       => $this->place_of_supply_state_id,
            'place_of_supply_state_id'   => $this->place_of_supply_state_id,
            'placeOfSupplyStateCode'     => $this->place_of_supply_state_code,
            'place_of_supply_state_code' => $this->place_of_supply_state_code,
            'invoiceType'                => $this->invoice_type,
            'invoice_type'               => $this->invoice_type,
            'isGstApplicable'            => (bool) $this->is_gst_applicable,
            'is_gst_applicable'          => (bool) $this->is_gst_applicable,
            'taxType'                    => $this->tax_type,
            'tax_type'                   => $this->tax_type,
            'is_inter_state'             => $this->tax_type === 'inter_state' || ((float) $this->igst_amount > 0),
            // Weights
            'totalGrossWeight'           => (float) $this->total_gross_weight,
            'total_gross_weight'         => (float) $this->total_gross_weight,
            'totalStoneWeight'           => (float) $this->total_stone_weight,
            'total_stone_weight'         => (float) $this->total_stone_weight,
            'totalNetWeight'             => (float) $this->total_net_weight,
            'total_net_weight'           => (float) $this->total_net_weight,
            'totalFineGoldWeight'        => (float) $this->total_fine_gold_weight,
            'total_fine_gold_weight'     => (float) $this->total_fine_gold_weight,
            // Charges and breakdown
            'totalGoldAmount'            => (float) $this->total_gold_amount,
            'total_gold_amount'          => (float) $this->total_gold_amount,
            'totalMakingCharges'         => (float) $this->total_making_charges,
            'total_making_charges'       => (float) $this->total_making_charges,
            'totalStoneCharges'          => (float) $this->total_stone_charges,
            'total_stone_charges'        => (float) $this->total_stone_charges,
            'hallmarkingCharges'         => (float) $this->hallmarking_charges,
            'hallmarking_charges'        => (float) $this->hallmarking_charges,
            'otherCharges'               => (float) $this->other_charges,
            'other_charges'              => (float) $this->other_charges,
            'discountAmount'             => (float) $this->discount_amount,
            'discount_amount'            => (float) $this->discount_amount,
            'taxableAmount'              => (float) $this->taxable_amount,
            'taxable_amount'             => (float) $this->taxable_amount,
            'totalTaxableAmount'         => (float) $this->taxable_amount,
            'total_taxable_amount'       => (float) $this->taxable_amount,
            // GST rates and amounts
            'cgstRate'                   => (float) $this->cgst_rate,
            'cgst_rate'                  => (float) $this->cgst_rate,
            'cgstAmount'                 => (float) $this->cgst_amount,
            'cgst_amount'                => (float) $this->cgst_amount,
            'sgstRate'                   => (float) $this->sgst_rate,
            'sgst_rate'                  => (float) $this->sgst_rate,
            'sgstAmount'                 => (float) $this->sgst_amount,
            'sgst_amount'                => (float) $this->sgst_amount,
            'igstRate'                   => (float) $this->igst_rate,
            'igst_rate'                  => (float) $this->igst_rate,
            'igstAmount'                 => (float) $this->igst_amount,
            'igst_amount'                => (float) $this->igst_amount,
            'totalTaxAmount'             => (float) $this->total_tax_amount,
            'total_tax_amount'           => (float) $this->total_tax_amount,
            'total_gst_amount'           => (float) $this->total_tax_amount,
            // Old gold exchange & totals
            'oldGoldExchangeAmount'      => (float) $this->old_gold_exchange_amount,
            'old_gold_exchange_amount'   => (float) $this->old_gold_exchange_amount,
            'total_old_gold_amount'      => (float) $this->old_gold_exchange_amount,
            'roundOff'                   => (float) $this->round_off,
            'round_off'                  => (float) $this->round_off,
            'grandTotal'                 => (float) $this->grand_total,
            'grand_total'                => (float) $this->grand_total,
            'paidAmount'                 => (float) $this->paid_amount,
            'paid_amount'                => (float) $this->paid_amount,
            'balanceAmount'              => (float) $this->balance_amount,
            'balance_amount'             => (float) $this->balance_amount,
            'due_amount'                 => (float) $this->balance_amount,
            'paymentStatus'              => $this->payment_status,
            'payment_status'             => $this->payment_status,
            'status'                     => $this->payment_status,
            'isCancelled'                => (bool) $this->is_cancelled,
            'is_cancelled'               => (bool) $this->is_cancelled,
            'cancellationReason'         => $this->cancellation_reason,
            'cancellation_reason'        => $this->cancellation_reason,
            'notes'                      => $this->notes,
            // Items and relations
            'items'                      => SaleItemResource::collection($this->whenLoaded('items')),
            'oldGoldExchanges'           => SaleOldGoldExchangeResource::collection($this->whenLoaded('oldGoldExchanges')),
            'old_gold_items'             => SaleOldGoldExchangeResource::collection($this->whenLoaded('oldGoldExchanges')),
            'createdAt'                  => $createdIso,
            'created_at'                 => $createdIso,
        ];
    }
}