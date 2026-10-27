<?php

namespace App\Http\Requests;

class UpdateBusinessProfileRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'         => 'sometimes|string|max:255',
            'trade_name'           => 'nullable|string|max:255',
            'legal_name'           => 'nullable|string|max:255',
            'tagline'              => 'nullable|string|max:255',
            'is_gst_registered'    => 'sometimes|boolean',
            'gst_scheme'           => 'sometimes|in:regular,composition,unregistered',
            'gstin'                => 'nullable|string|size:15',
            'pan_number'           => 'nullable|string|size:10',
            'hallmark_license_no'  => 'nullable|string|max:50',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:150',
            'address_line1'        => 'nullable|string|max:255',
            'address_line2'        => 'nullable|string|max:255',
            'city'                 => 'nullable|string|max:100',
            'district_id'          => 'nullable|integer|exists:districts,id',
            'state_id'             => 'nullable|integer|exists:states,id',
            'state_code'           => 'nullable|integer',
            'pincode'              => 'nullable|string|max:10',
            'currency_symbol'      => 'sometimes|string|max:10',
            'invoice_prefix'       => 'sometimes|string|max:20',
            'terms_and_conditions' => 'nullable|string',
        ];
    }
}
