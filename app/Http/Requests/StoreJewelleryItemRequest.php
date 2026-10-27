<?php

namespace App\Http\Requests;

class StoreJewelleryItemRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag_code'                  => 'required|string|max:50|unique:jewellery_items,tag_code',
            'item_name'                 => 'required|string|max:200',
            'category_id'               => 'required|integer|exists:jewellery_categories,id',
            'purity_id'                 => 'required|integer|exists:purities,id',
            'hsn_code'                  => 'sometimes|string|max:10',
            'gross_weight'              => 'required|numeric|gt:0',
            'stone_weight'              => 'nullable|numeric|min:0|lt:gross_weight',
            'stone_value'               => 'nullable|numeric|min:0',
            'making_charge_type'        => 'sometimes|in:per_gram,percentage,fixed',
            'making_charge_rate'        => 'nullable|numeric|min:0',
            'purchase_cost'             => 'nullable|numeric|min:0',
            'selling_min_rate_per_gram' => 'nullable|numeric|min:0',
            'huid'                      => 'nullable|string|max:20',
            'source_type'               => 'sometimes|in:purchase,production,opening_stock,old_gold_exchange',
            'status'                    => 'sometimes|in:in_stock,sold,returned_to_vendor,damaged',
            'notes'                     => 'nullable|string',
        ];
    }
}
