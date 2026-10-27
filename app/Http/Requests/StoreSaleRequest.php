<?php

namespace App\Http\Requests;

class StoreSaleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number'             => 'nullable|string|max:50|unique:sales,invoice_number',
            'invoice_date'               => 'required|date',
            'party_id'                   => 'nullable|integer|exists:parties,id',
            'customer_name'              => 'required|string|max:200',
            'customer_mobile'            => 'nullable|string|max:20',
            'customer_email'             => 'nullable|email|max:150',
            'customer_address'           => 'nullable|string',
            'customer_gstin'             => 'nullable|string|size:15',
            'customer_pan'               => [
                'nullable',
                'string',
                'size:10',
                function ($attribute, $value, $fail) {
                    // Indian Tax Regulation: Cash receipts >= 2,00,000 require customer PAN
                    $mode = strtolower((string) $this->input('payment_mode'));
                    $paid = (float) $this->input('paid_amount', 0);
                    if ($mode === 'cash' && $paid >= 200000 && empty($value)) {
                        $fail('Customer PAN is legally mandatory for cash payments of ₹2,00,000 or more.');
                    }
                },
            ],
            'place_of_supply_state_id'   => 'nullable|integer|exists:states,id',
            'place_of_supply_state_code' => 'nullable|integer',
            'invoice_type'               => 'sometimes|in:tax_invoice,bill_of_supply,cash_memo',
            'is_gst_applicable'          => 'sometimes|boolean',
            'tax_type'                   => 'sometimes|in:intra_state,inter_state,non_gst',
            'discount_amount'            => 'nullable|numeric|min:0',
            'paid_amount'                => 'nullable|numeric|min:0',
            'payment_account_id'         => 'nullable|integer|exists:accounts,id',
            'payment_mode'               => 'nullable|in:cash,bank_transfer,upi,card,cheque',
            'transaction_reference'      => 'nullable|string|max:100',
            'notes'                      => 'nullable|string',

            // Multi-Area POS Terminal metadata (optional)
            'terminal_id'                => 'nullable|string|max:50',
            'terminal_code'              => 'nullable|string|max:50',
            'terminal_name'              => 'nullable|string|max:100',
            'terminal_area'              => 'nullable|string|max:100',
            'cashier_name'               => 'nullable|string|max:100',

            // Sale Items
            'items'                      => 'required|array|min:1',
            'items.*.jewellery_item_id'  => 'nullable|integer|exists:jewellery_items,id',
            'items.*.category_id'        => 'nullable|integer|exists:jewellery_categories,id',
            'items.*.purity_id'          => 'required|integer|exists:purities,id',
            'items.*.item_name'          => 'required|string|max:255',
            'items.*.tag_code'           => 'nullable|string|max:50',
            'items.*.huid'               => 'nullable|string|max:20',
            'items.*.hsn_code'           => 'sometimes|string|max:10',
            'items.*.gross_weight'       => 'required|numeric|gt:0',
            'items.*.stone_weight'       => 'nullable|numeric|min:0',
            'items.*.gold_rate_per_gram' => 'required|numeric|gt:0',
            'items.*.making_charge_type' => 'sometimes|in:per_gram,percentage,fixed',
            'items.*.making_charge_rate' => 'nullable|numeric|min:0',
            'items.*.making_charges'     => 'nullable|numeric|min:0',
            'items.*.stone_charges'      => 'nullable|numeric|min:0',
            'items.*.hallmarking_charge' => 'nullable|numeric|min:0',
            'items.*.discount_amount'    => 'nullable|numeric|min:0',
            'items.*.gst_rate'           => 'nullable|numeric|min:0',

            // Old Gold Trade-In / Exchanges (Optional)
            'old_gold_exchanges'         => 'nullable|array',
            'old_gold_exchanges.*.item_description' => 'required_with:old_gold_exchanges|string|max:255',
            'old_gold_exchanges.*.gross_weight'     => 'required_with:old_gold_exchanges|numeric|gt:0',
            'old_gold_exchanges.*.dust_stone_weight'=> 'nullable|numeric|min:0',
            'old_gold_exchanges.*.purity_percentage'=> 'required_with:old_gold_exchanges|numeric|gt:0|lte:100',
            'old_gold_exchanges.*.buying_rate_per_gram' => 'required_with:old_gold_exchanges|numeric|gt:0',
            'old_gold_exchanges.*.remarks'          => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $merge = [];

        // Auto-default invoice date to today if omitted
        if (!$this->has('invoice_date') || empty($this->invoice_date)) {
            $merge['invoice_date'] = now()->format('Y-m-d');
        }

        // Account mapping
        if (!$this->has('payment_account_id') && $this->has('deposit_account_id')) {
            $merge['payment_account_id'] = $this->deposit_account_id;
        }

        // Default to first active account if omitted
        if ((!$this->has('payment_account_id') || empty($this->payment_account_id)) && $this->has('paid_amount') && (float) $this->paid_amount > 0) {
            $defaultAcc = \App\Models\Account::first();
            if ($defaultAcc) {
                $merge['payment_account_id'] = $defaultAcc->id;
            }
        }

        // Payment mode normalization
        if ($this->has('payment_mode')) {
            $merge['payment_mode'] = strtolower((string) $this->payment_mode);
        }

        if ($this->has('invoice_type') && !in_array($this->invoice_type, ['tax_invoice', 'bill_of_supply', 'cash_memo'])) {
            $merge['invoice_type'] = 'tax_invoice';
        }

        // Items normalization
        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as &$item) {
                if (!isset($item['gold_rate_per_gram']) && isset($item['rate_per_gram'])) {
                    $item['gold_rate_per_gram'] = $item['rate_per_gram'];
                }
            }
            $merge['items'] = $items;
        }

        // Handle old gold alias and empty arrays
        $rawExchanges = $this->input('old_gold_exchanges') ?? $this->input('old_gold_items');
        if (is_array($rawExchanges) && count($rawExchanges) > 0) {
            $exchanges = $rawExchanges;
            foreach ($exchanges as &$ex) {
                if (!isset($ex['item_description']) && isset($ex['description'])) {
                    $ex['item_description'] = $ex['description'];
                }
                if (!isset($ex['buying_rate_per_gram']) && isset($ex['rate_per_gram'])) {
                    $ex['buying_rate_per_gram'] = $ex['rate_per_gram'];
                }
                if (!isset($ex['dust_stone_weight']) && isset($ex['dust_weight'])) {
                    $ex['dust_stone_weight'] = $ex['dust_weight'];
                }
                if (!isset($ex['purity_percentage']) && isset($ex['purity_percent'])) {
                    $ex['purity_percentage'] = $ex['purity_percent'];
                }
                if (!isset($ex['purity_percentage']) && isset($ex['purity_id'])) {
                    $p = \App\Models\Purity::find($ex['purity_id']);
                    if ($p) {
                        $ex['purity_percentage'] = $p->purity_percentage;
                    }
                }
            }
            $merge['old_gold_exchanges'] = $exchanges;
        } else {
            // Nullify so validation rule required_with won't fail on empty array
            $merge['old_gold_exchanges'] = null;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}