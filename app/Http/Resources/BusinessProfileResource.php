<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $addressFull = $this->address_line1;
        if ($this->address_line2) {
            $addressFull = $addressFull ? "{$addressFull}, {$this->address_line2}" : $this->address_line2;
        }

        return [
            'id'                   => $this->id,

            // Company & Trade Name
            'companyName'          => $this->company_name,
            'company_name'         => $this->company_name,
            'businessName'         => $this->company_name,
            'business_name'        => $this->company_name,
            'name'                 => $this->company_name,
            'tradeName'            => $this->trade_name,
            'trade_name'           => $this->trade_name,
            'legalName'            => $this->legal_name,
            'legal_name'           => $this->legal_name,
            'tagline'              => $this->tagline,

            // Tax & Registration
            'isGstRegistered'      => (bool) $this->is_gst_registered,
            'is_gst_registered'    => (bool) $this->is_gst_registered,
            'gstScheme'            => $this->gst_scheme,
            'gst_scheme'           => $this->gst_scheme,
            'gstin'                => $this->gstin,
            'panNumber'            => $this->pan_number,
            'pan_number'           => $this->pan_number,
            'pan'                  => $this->pan_number,
            'hallmarkLicenseNo'    => $this->hallmark_license_no,
            'hallmark_license_no'  => $this->hallmark_license_no,

            // Contact
            'phone'                => $this->phone,
            'email'                => $this->email,
            'website'              => $this->website,

            // Address & Jurisdiction
            'address'              => $addressFull,
            'addressLine1'         => $this->address_line1,
            'address_line1'        => $this->address_line1,
            'addressLine2'         => $this->address_line2,
            'address_line2'        => $this->address_line2,
            'city'                 => $this->city,
            'districtId'           => $this->district_id,
            'district_id'          => $this->district_id,
            'stateId'              => $this->state_id,
            'state_id'             => $this->state_id,
            'stateCode'            => $this->state_code,
            'state_code'           => $this->state_code,
            'state'                => $this->state_name ?: ($this->state?->state_name ?: ($this->state_code == 19 ? 'West Bengal' : null)),
            'stateName'            => $this->state_name ?: ($this->state?->state_name ?: ($this->state_code == 19 ? 'West Bengal' : null)),
            'state_name'           => $this->state_name ?: ($this->state?->state_name ?: ($this->state_code == 19 ? 'West Bengal' : null)),
            'pincode'              => $this->pincode,

            // Bank Accounts & UPI
            'bankName'             => $this->bank_name,
            'bank_name'            => $this->bank_name,
            'bankAccountNumber'    => $this->bank_account_number,
            'bank_account_number'  => $this->bank_account_number,
            'bankIfsc'             => $this->bank_ifsc,
            'bank_ifsc'            => $this->bank_ifsc,
            'bankBranch'           => $this->bank_branch,
            'bank_branch'          => $this->bank_branch,
            'upiId'                => $this->upi_id,
            'upi_id'               => $this->upi_id,

            // Metal Trading Scope
            'tradedMetals'         => $this->traded_metals ?: 'both',
            'traded_metals'        => $this->traded_metals ?: 'both',

            // Invoicing & Formats
            'currencySymbol'       => $this->currency_symbol ?: '₹',
            'currency_symbol'      => $this->currency_symbol ?: '₹',
            'invoicePrefix'        => $this->invoice_prefix,
            'invoice_prefix'       => $this->invoice_prefix,
            'purchasePrefix'       => $this->purchase_prefix,
            'purchase_prefix'      => $this->purchase_prefix,
            'stockEntryPrefix'     => $this->stock_entry_prefix,
            'stock_entry_prefix'   => $this->stock_entry_prefix,
            'financialYearStart'   => $this->financial_year_start?->format('Y-m-d'),
            'financial_year_start' => $this->financial_year_start?->format('Y-m-d'),
            'termsAndConditions'   => $this->terms_and_conditions,
            'terms_and_conditions' => $this->terms_and_conditions,
        ];
    }
}
