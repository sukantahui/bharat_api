<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'companyName'        => $this->company_name,
            'tradeName'          => $this->trade_name,
            'legalName'          => $this->legal_name,
            'tagline'            => $this->tagline,
            'isGstRegistered'    => (bool) $this->is_gst_registered,
            'gstScheme'          => $this->gst_scheme,
            'gstin'              => $this->gstin,
            'panNumber'          => $this->pan_number,
            'hallmarkLicenseNo'  => $this->hallmark_license_no,
            'phone'              => $this->phone,
            'email'              => $this->email,
            'addressLine1'       => $this->address_line1,
            'addressLine2'       => $this->address_line2,
            'city'               => $this->city,
            'districtId'         => $this->district_id,
            'stateId'            => $this->state_id,
            'stateCode'          => $this->state_code,
            'pincode'            => $this->pincode,
            'currencySymbol'     => $this->currency_symbol,
            'invoicePrefix'      => $this->invoice_prefix,
            'purchasePrefix'     => $this->purchase_prefix,
            'stockEntryPrefix'   => $this->stock_entry_prefix,
            'financialYearStart' => $this->financial_year_start?->format('Y-m-d'),
            'termsAndConditions' => $this->terms_and_conditions,
        ];
    }
}
