<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'partyType'                => $this->party_type,
            'name'                     => $this->name,
            'tradeName'                => $this->trade_name,
            'mobile'                   => $this->mobile,
            'phone'                    => $this->phone,
            'email'                    => $this->email,
            'address'                  => $this->address,
            'city'                     => $this->city,
            'districtId'               => $this->district_id,
            'stateId'                  => $this->state_id,
            'stateCode'                => $this->state_code,
            'pincode'                  => $this->pincode,
            'gstType'                  => $this->gst_type,
            'gstin'                    => $this->gstin,
            'panNumber'                => $this->pan_number,
            'aadhaarNumber'            => $this->aadhaar_number,
            'openingBalance'           => (float) $this->opening_balance,
            'openingBalanceType'       => $this->opening_balance_type,
            'currentBalance'           => (float) $this->current_balance,
            'metalOpeningBalanceGrams' => (float) $this->metal_opening_balance_grams,
            'metalCurrentBalanceGrams' => (float) $this->metal_current_balance_grams,
            'isActive'                 => (bool) $this->is_active,
            'notes'                    => $this->notes,
        ];
    }
}
