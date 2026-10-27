<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'accountName'        => $this->account_name,
            'accountCode'        => $this->account_code,
            'accountGroupId'     => $this->account_group_id,
            'accountType'        => $this->account_type,
            'bankName'           => $this->bank_name,
            'accountNumber'      => $this->account_number,
            'ifscCode'           => $this->ifsc_code,
            'branch'             => $this->branch,
            'openingBalance'     => (float) $this->opening_balance,
            'openingBalanceType' => $this->opening_balance_type,
            'currentBalance'     => (float) $this->current_balance,
            'isActive'           => (bool) $this->is_active,
            'isSystem'           => (bool) $this->is_system,
            'description'        => $this->description,
        ];
    }
}
