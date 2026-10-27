<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseIncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'entryNumber'      => $this->entry_number,
            'entryDate'        => $this->entry_date?->format('Y-m-d'),
            'entryType'        => $this->entry_type,
            'accountId'        => $this->account_id,
            'account'          => new AccountResource($this->whenLoaded('account')),
            'paymentAccountId' => $this->payment_account_id,
            'paymentAccount'   => new AccountResource($this->whenLoaded('paymentAccount')),
            'amount'           => (float) $this->amount,
            'paymentMode'      => $this->payment_mode,
            'referenceNo'      => $this->reference_no,
            'description'      => $this->description,
            'isGstClaimed'     => (bool) $this->is_gst_claimed,
            'taxAmount'        => (float) $this->tax_amount,
            'vendorGstin'      => $this->vendor_gstin,
            'createdAt'        => $this->created_at?->toIso8601String(),
        ];
    }
}
