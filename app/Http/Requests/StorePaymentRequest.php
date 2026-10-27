<?php

namespace App\Http\Requests;

class StorePaymentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_number'        => 'nullable|string|max:50|unique:payments,payment_number',
            'payment_date'          => 'required|date',
            'payment_type'          => 'required|in:receipt,payment',
            'account_id'            => 'required|integer|exists:accounts,id',
            'party_id'              => 'nullable|integer|exists:parties,id',
            'reference_type'        => 'sometimes|in:sale,purchase,expense,income,advance,other',
            'reference_id'          => 'nullable|integer',
            'payment_mode'          => 'required|in:cash,bank_transfer,upi,card,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'amount'                => 'required|numeric|gt:0',
            'notes'                 => 'nullable|string',
        ];
    }
}
