<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'creditNoteNo'           => $this->credit_note_no,
            'creditNoteDate'         => $this->credit_note_date?->format('Y-m-d'),
            'saleId'                 => $this->sale_id,
            'partyId'                => $this->party_id,
            'customerName'           => $this->customer_name,
            'customerMobile'         => $this->customer_mobile,
            'customerGstin'          => $this->customer_gstin,
            'originalInvoiceNo'      => $this->original_invoice_no,
            'originalInvoiceDate'    => $this->original_invoice_date?->format('Y-m-d'),
            'placeOfSupplyStateId'   => $this->place_of_supply_state_id,
            'placeOfSupplyStateCode' => $this->place_of_supply_state_code,
            'isGstApplicable'        => (bool) $this->is_gst_applicable,
            'taxType'                => $this->tax_type,
            'totalGrossWeight'       => (float) $this->total_gross_weight,
            'totalStoneWeight'       => (float) $this->total_stone_weight,
            'totalNetWeight'         => (float) $this->total_net_weight,
            'totalFineGoldWeight'    => (float) $this->total_fine_gold_weight,
            'taxableAmount'          => (float) $this->taxable_amount,
            'cgstRate'               => (float) $this->cgst_rate,
            'cgstAmount'             => (float) $this->cgst_amount,
            'sgstRate'               => (float) $this->sgst_rate,
            'sgstAmount'             => (float) $this->sgst_amount,
            'igstRate'               => (float) $this->igst_rate,
            'igstAmount'             => (float) $this->igst_amount,
            'totalTaxAmount'         => (float) $this->total_tax_amount,
            'roundOff'               => (float) $this->round_off,
            'totalCreditAmount'      => (float) $this->total_credit_amount,
            'refundMode'             => $this->refund_mode,
            'refundPaymentId'        => $this->refund_payment_id,
            'returnReason'           => $this->return_reason,
            'notes'                  => $this->notes,
            'createdAt'              => $this->created_at?->toIso8601String(),
        ];
    }
}
