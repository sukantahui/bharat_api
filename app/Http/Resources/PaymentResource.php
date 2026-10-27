<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'paymentNumber'        => $this->payment_number,
            'paymentDate'          => $this->payment_date?->format('Y-m-d'),
            'paymentType'          => $this->payment_type,
            'accountId'            => $this->account_id,
            'account'              => new AccountResource($this->whenLoaded('account')),
            'partyId'              => $this->party_id,
            'party'                => new PartyResource($this->whenLoaded('party')),
            'referenceType'        => $this->reference_type,
            'referenceId'          => $this->reference_id,
            'paymentMode'          => $this->payment_mode,
            'transactionReference' => $this->transaction_reference,
            'amount'               => (float) $this->amount,
            'notes'                => $this->notes,
            'createdAt'            => $this->created_at?->toIso8601String(),
        ];
    }
}
