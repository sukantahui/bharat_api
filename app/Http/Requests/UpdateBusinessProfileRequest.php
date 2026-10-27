<?php

namespace App\Http\Requests;

class UpdateBusinessProfileRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        // Company Name aliases
        if ($this->has('business_name') && !$this->has('company_name')) {
            $merge['company_name'] = $this->input('business_name');
        } elseif ($this->has('businessName') && !$this->has('company_name')) {
            $merge['company_name'] = $this->input('businessName');
        } elseif ($this->has('companyName') && !$this->has('company_name')) {
            $merge['company_name'] = $this->input('companyName');
        }

        // PAN aliases
        if ($this->has('pan') && !$this->has('pan_number')) {
            $merge['pan_number'] = strtoupper(trim((string) $this->input('pan')));
        } elseif ($this->has('panNumber') && !$this->has('pan_number')) {
            $merge['pan_number'] = strtoupper(trim((string) $this->input('panNumber')));
        }

        // Address aliases
        if ($this->has('address') && !$this->has('address_line1')) {
            $merge['address_line1'] = $this->input('address');
            $merge['address_line2'] = null; // Clear line2 so address isn't duplicated
        } elseif ($this->has('addressLine1') && !$this->has('address_line1')) {
            $merge['address_line1'] = $this->input('addressLine1');
        }
        if ($this->has('addressLine2') && !$this->has('address_line2')) {
            $merge['address_line2'] = $this->input('addressLine2');
        }

        // State code and name aliases
        if ($this->has('stateCode') && !$this->has('state_code')) {
            $merge['state_code'] = (int) $this->input('stateCode');
        }
        if ($this->has('state') && !$this->has('state_name')) {
            $merge['state_name'] = $this->input('state');
        } elseif ($this->has('stateName') && !$this->has('state_name')) {
            $merge['state_name'] = $this->input('stateName');
        }

        // BIS Hallmark
        if ($this->has('hallmarkLicenseNo') && !$this->has('hallmark_license_no')) {
            $merge['hallmark_license_no'] = $this->input('hallmarkLicenseNo');
        }

        // Bank fields aliases
        if ($this->has('bankName') && !$this->has('bank_name')) {
            $merge['bank_name'] = $this->input('bankName');
        }
        if ($this->has('bankAccountNumber') && !$this->has('bank_account_number')) {
            $merge['bank_account_number'] = $this->input('bankAccountNumber');
        }
        if ($this->has('bankIfsc') && !$this->has('bank_ifsc')) {
            $merge['bank_ifsc'] = strtoupper(trim((string) $this->input('bankIfsc')));
        }
        if ($this->has('bankBranch') && !$this->has('bank_branch')) {
            $merge['bank_branch'] = $this->input('bankBranch');
        }
        if ($this->has('upiId') && !$this->has('upi_id')) {
            $merge['upi_id'] = $this->input('upiId');
        }

        // Metals scope
        if ($this->has('tradedMetals') && !$this->has('traded_metals')) {
            $merge['traded_metals'] = $this->input('tradedMetals');
        }

        // Terms and conditions
        if ($this->has('termsAndConditions') && !$this->has('terms_and_conditions')) {
            $merge['terms_and_conditions'] = $this->input('termsAndConditions');
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'company_name'         => 'sometimes|string|max:255',
            'business_name'        => 'sometimes|string|max:255',
            'trade_name'           => 'nullable|string|max:255',
            'legal_name'           => 'nullable|string|max:255',
            'tagline'              => 'nullable|string|max:255',
            'is_gst_registered'    => 'sometimes|boolean',
            'gst_scheme'           => 'sometimes|in:regular,composition,unregistered',
            'gstin'                => 'nullable|string|size:15',
            'pan_number'           => 'nullable|string|size:10',
            'pan'                  => 'nullable|string|size:10',
            'hallmark_license_no'  => 'nullable|string|max:50',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:150',
            'website'              => 'nullable|string|max:255',
            'address_line1'        => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:255',
            'address_line2'        => 'nullable|string|max:255',
            'city'                 => 'nullable|string|max:100',
            'district_id'          => 'nullable|integer|exists:districts,id',
            'state_id'             => 'nullable|integer|exists:states,id',
            'state_code'           => 'nullable|integer',
            'state_name'           => 'nullable|string|max:100',
            'state'                => 'nullable|string|max:100',
            'pincode'              => 'nullable|string|max:10',
            'currency_symbol'      => 'sometimes|string|max:10',
            'invoice_prefix'       => 'sometimes|string|max:20',
            'purchase_prefix'      => 'sometimes|string|max:20',
            'stock_entry_prefix'   => 'sometimes|string|max:20',
            'voucher_prefix'       => 'sometimes|string|max:20',
            'terms_and_conditions' => 'nullable|string',
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:50',
            'bank_ifsc'            => 'nullable|string|max:20',
            'bank_branch'          => 'nullable|string|max:255',
            'upi_id'               => 'nullable|string|max:100',
            'traded_metals'        => 'nullable|string|in:gold,silver,both',
        ];
    }
}
