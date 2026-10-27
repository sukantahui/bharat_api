<?php

namespace App\Http\Requests;

class StoreExpenseIncomeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_number'       => 'nullable|string|max:50|unique:expense_incomes,entry_number',
            'entry_date'         => 'required|date',
            'entry_type'         => 'required|in:expense,income',
            'account_id'         => 'required|integer|exists:accounts,id', // Expense or Income head
            'payment_account_id' => 'required|integer|exists:accounts,id', // Cash or Bank account
            'amount'             => 'required|numeric|gt:0',
            'payment_mode'       => 'required|in:cash,bank_transfer,upi,card,cheque',
            'reference_no'       => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'is_gst_claimed'     => 'sometimes|boolean',
            'tax_amount'         => 'nullable|numeric|min:0',
            'vendor_gstin'       => 'nullable|string|size:15',
        ];
    }
}
