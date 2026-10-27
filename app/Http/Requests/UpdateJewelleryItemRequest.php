<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateJewelleryItemRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item') ?? $this->route('id') ?? $this->input('id');

        return [
            'tag_code'                  => ['sometimes', 'string', 'max:50', Rule::unique('jewellery_items', 'tag_code')->ignore($itemId)],
            'item_name'                 => 'sometimes|string|max:200',
            'category_id'               => 'sometimes|integer|exists:jewellery_categories,id',
            'purity_id'                 => 'sometimes|integer|exists:purities,id',
            'hsn_code'                  => 'sometimes|string|max:10',
            'gross_weight'              => 'sometimes|numeric|gt:0',
            'stone_weight'              => 'nullable|numeric|min:0',
            'stone_value'               => 'nullable|numeric|min:0',
            'making_charge_type'        => 'sometimes|in:per_gram,percentage,fixed',
            'making_charge_rate'        => 'nullable|numeric|min:0',
            'purchase_cost'             => 'nullable|numeric|min:0',
            'selling_min_rate_per_gram' => 'nullable|numeric|min:0',
            'huid'                      => 'nullable|string|max:20',
            'status'                    => 'sometimes|in:in_stock,sold,returned_to_vendor,damaged',
            'notes'                     => 'nullable|string',
        ];
    }
}
