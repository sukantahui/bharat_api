<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialVoucher;
use App\Models\FinancialVoucherEntry;
use App\Models\Party;
use Illuminate\Support\Facades\DB;

class AccountingLedgerService
{
    /**
     * Post a balanced double-entry financial voucher.
     *
     * @param string $voucherType 'receipt'|'payment'|'contra'|'sales_journal'|'purchase_journal'|'expense'|'income'|'journal'
     * @param string $referenceType 'sale'|'purchase'|'expense_income'|'payment'|'direct'
     * @param int|null $referenceId
     * @param array $entries Array of ['account_id' => int, 'entry_type' => 'debit'|'credit', 'amount' => float, 'narration' => string]
     * @param string|null $narration
     * @param string|null $voucherDate
     * @return FinancialVoucher
     */
    public function postVoucher(
        string $voucherType,
        string $referenceType,
        ?int $referenceId,
        array $entries,
        ?string $narration = null,
        ?string $voucherDate = null
    ): FinancialVoucher {
        return DB::transaction(function () use ($voucherType, $referenceType, $referenceId, $entries, $narration, $voucherDate) {
            $totalDebit  = 0.00;
            $totalCredit = 0.00;

            foreach ($entries as $e) {
                $amt = (float) $e['amount'];
                if ($e['entry_type'] === 'debit') {
                    $totalDebit += $amt;
                } else {
                    $totalCredit += $amt;
                }
            }

            $voucherNo = 'VCH-' . strtoupper(substr($voucherType, 0, 3)) . '-' . now()->format('YmdHis') . '-' . rand(10, 99);

            $voucher = FinancialVoucher::create([
                'voucher_number' => $voucherNo,
                'voucher_date'   => $voucherDate ?? now()->format('Y-m-d'),
                'voucher_type'   => $voucherType,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'total_amount'   => max($totalDebit, $totalCredit),
                'narration'      => $narration,
                'created_by'     => auth()->id(),
            ]);

            foreach ($entries as $e) {
                $amt = (float) $e['amount'];
                if ($amt <= 0) {
                    continue;
                }

                FinancialVoucherEntry::create([
                    'financial_voucher_id' => $voucher->id,
                    'account_id'           => $e['account_id'],
                    'entry_type'           => $e['entry_type'],
                    'amount'               => $amt,
                    'narration'            => $e['narration'] ?? null,
                ]);

                // Update account running balance
                $account = Account::find($e['account_id']);
                if ($account) {
                    if ($e['entry_type'] === 'debit') {
                        $account->increment('current_balance', $amt);
                    } else {
                        $account->decrement('current_balance', $amt);
                    }
                }
            }

            return $voucher;
        });
    }

    /**
     * Adjust party ledger balance (Receivable vs Payable).
     */
    public function adjustPartyBalance(int $partyId, float $amount, string $direction = 'increase'): void
    {
        $party = Party::find($partyId);
        if ($party) {
            if ($direction === 'increase') {
                $party->increment('current_balance', $amount);
            } else {
                $party->decrement('current_balance', $amount);
            }
        }
    }
}
