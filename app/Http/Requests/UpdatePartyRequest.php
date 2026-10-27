<?php

namespace App\Http\Requests;

class UpdatePartyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_type'           => 'sometimes|in:customer,vendor,artisan,both',
            'name'                 => 'sometimes|string|max:200',
            'trade_name'           => 'nullable|string|max:200',
            'mobile'               => 'nullable|string|max:20',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:150',
            'address'              => 'nullable|string',
            'city'                 => 'nullable|string|max:100',
            'district_id'          => 'nullable|integer|exists:districts,id',
            'state_id'             => 'nullable|integer|exists:states,id',
            'state_code'           => 'nullable|integer',
            'pincode'              => 'nullable|string|max:10',
            'gst_type'             => 'sometimes|in:unregistered,registered_regular,registered_composition,consumer',
            'gstin'                => 'nullable|string|size:15',
            'pan_number'           => 'nullable|string|size:10',
            'aadhaar_number'       => 'nullable|string|max:20',
            'is_active'            => 'sometimes|boolean',
            'notes'                => 'nullable|string',
        ];
    }
}
