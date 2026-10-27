<?php

namespace App\Http\Requests;

class StoreDailyGoldRateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rate_date'             => 'required|date',
            'purity_id'             => 'required|integer|exists:purities,id',
            'metal_type'            => 'sometimes|in:gold,silver,platinum',
            'selling_rate_per_gram' => 'required_without:rate_per_gram|numeric|min:1',
            'rate_per_gram'         => 'sometimes|numeric|min:1',
            'buying_rate_per_gram'  => 'required_without:buyback_rate_per_gram|numeric|min:1',
            'buyback_rate_per_gram' => 'sometimes|numeric|min:1',
            'effective_time'        => 'nullable|string',
            'notes'                 => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $merge = [];
        if (!$this->has('selling_rate_per_gram') && $this->has('rate_per_gram')) {
            $merge['selling_rate_per_gram'] = $this->rate_per_gram;
        }
        if (!$this->has('buying_rate_per_gram') && $this->has('buyback_rate_per_gram')) {
            $merge['buying_rate_per_gram'] = $this->buyback_rate_per_gram;
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
