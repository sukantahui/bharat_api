<?php

namespace App\Http\Requests;

class StorePurchaseRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_invoice_no'    => 'nullable|string|max:50|unique:purchases,purchase_invoice_no',
            'purchase_date'          => 'required|date',
            'vendor_bill_no'         => 'nullable|string|max:50',
            'vendor_bill_date'       => 'nullable|date',
            'party_id'               => 'required|integer|exists:parties,id',
            'purchase_type'          => 'sometimes|in:vendor_jewellery,bullion_raw_gold,customer_old_gold_buyback',
            'is_gst_applicable'     => 'sometimes|boolean',
            'tax_type'               => 'sometimes|in:intra_state,inter_state,non_gst',
            'other_charges'          => 'nullable|numeric|min:0',
            'discount_amount'        => 'nullable|numeric|min:0',
            'paid_amount'            => 'nullable|numeric|min:0',
            'payment_account_id'     => 'nullable|integer|exists:accounts,id', // Cash/Bank account if paid
            'payment_mode'           => 'nullable|in:cash,bank_transfer,upi,card,cheque',
            'itc_eligibility'        => 'sometimes|in:inputs,capital_goods,input_services,ineligible',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.category_id'    => 'nullable|integer|exists:jewellery_categories,id',
            'items.*.purity_id'      => 'required|integer|exists:purities,id',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.hsn_code'       => 'sometimes|string|max:10',
            'items.*.gross_weight'   => 'required|numeric|gt:0',
            'items.*.stone_weight'   => 'nullable|numeric|min:0',
            'items.*.rate_per_gram'  => 'required|numeric|gt:0',
            'items.*.making_charges' => 'nullable|numeric|min:0',
            'items.*.stone_charges'  => 'nullable|numeric|min:0',
            'items.*.gst_rate'       => 'nullable|numeric|min:0',
            'items.*.create_stock_item' => 'sometimes|boolean', // if true, creates in jewellery_items
            'items.*.tag_code'       => 'nullable|string|max:50|unique:jewellery_items,tag_code',
        ];
    }
}
