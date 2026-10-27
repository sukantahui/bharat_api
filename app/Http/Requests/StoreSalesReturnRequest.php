<?php

namespace App\Http\Requests;

class StoreSalesReturnRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_note_no'           => 'nullable|string|max:50|unique:sales_returns,credit_note_no',
            'credit_note_date'         => 'required|date',
            'sale_id'                  => 'required|integer|exists:sales,id',
            'party_id'                 => 'nullable|integer|exists:parties,id',
            'refund_mode'              => 'sometimes|in:store_credit_balance,cash_refund,bank_refund,replacement_exchange',
            'refund_account_id'        => 'nullable|integer|exists:accounts,id', // Cash or Bank account if refund paid
            'return_reason'            => 'sometimes|in:exchange,defective,size_issue,customer_return,other',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.sale_item_id'     => 'nullable|integer|exists:sale_items,id',
            'items.*.jewellery_item_id'=> 'nullable|integer|exists:jewellery_items,id',
            'items.*.category_id'      => 'nullable|integer|exists:jewellery_categories,id',
            'items.*.purity_id'        => 'required|integer|exists:purities,id',
            'items.*.item_name'        => 'required|string|max:255',
            'items.*.tag_code'         => 'nullable|string|max:50',
            'items.*.gross_weight'     => 'required|numeric|gt:0',
            'items.*.stone_weight'     => 'nullable|numeric|min:0',
            'items.*.rate_per_gram'    => 'required|numeric|gt:0',
            'items.*.making_charges_refunded' => 'nullable|numeric|min:0',
            'items.*.stone_charges_refunded'  => 'nullable|numeric|min:0',
            'items.*.gst_rate'         => 'nullable|numeric|min:0',
            'items.*.stock_action'     => 'sometimes|in:restocked_as_original,converted_to_scrap,sent_for_repair,none',
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as &$item) {
                if (!isset($item['rate_per_gram']) && isset($item['gold_rate_per_gram'])) {
                    $item['rate_per_gram'] = $item['gold_rate_per_gram'];
                }
            }
            $this->merge(['items' => $items]);
        }
    }
}
