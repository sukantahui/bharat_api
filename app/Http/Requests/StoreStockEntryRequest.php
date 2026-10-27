<?php

namespace App\Http\Requests;

class StoreStockEntryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_number'               => 'nullable|string|max:50|unique:stock_entries,entry_number',
            'entry_date'                 => 'required|date',
            'entry_type'                 => 'sometimes|in:production_inward,opening_stock,surplus_adjustment,damage_writeoff',
            'artisan_id'                 => 'nullable|integer|exists:parties,id',
            'remarks'                    => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.category_id'        => 'required|integer|exists:jewellery_categories,id',
            'items.*.purity_id'          => 'required|integer|exists:purities,id',
            'items.*.item_name'          => 'required|string|max:200',
            'items.*.tag_code'           => 'nullable|string|max:50|unique:jewellery_items,tag_code',
            'items.*.huid'               => 'nullable|string|max:20',
            'items.*.gross_weight'       => 'required|numeric|gt:0',
            'items.*.stone_weight'       => 'nullable|numeric|min:0',
            'items.*.estimated_rate_per_gram' => 'nullable|numeric|min:0',
            'items.*.making_cost'        => 'nullable|numeric|min:0',
            'items.*.stone_cost'         => 'nullable|numeric|min:0',
            'items.*.notes'              => 'nullable|string|max:255',
        ];
    }
}
