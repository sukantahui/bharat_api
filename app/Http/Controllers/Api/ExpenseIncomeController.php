<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseIncomeRequest;
use App\Http\Resources\ExpenseIncomeResource;
use App\Models\Account;
use App\Models\ExpenseIncome;
use App\Services\AccountingLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseIncomeController extends Controller
{
    public function __construct(
        protected AccountingLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = ExpenseIncome::with(['account', 'paymentAccount']);

        if ($request->filled('type')) {
            $query->where('entry_type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('payment_account_id')) {
            $query->where('payment_account_id', $request->payment_account_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('entry_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('entry_date', '<=', $request->to_date);
        }

        $records = $query->orderBy('entry_date', 'desc')->paginate(30);
        return ResponseHelper::success('Expense/Income entries retrieved successfully', ExpenseIncomeResource::collection($records));
    }

    public function show($id)
    {
        $entry = ExpenseIncome::with(['account', 'paymentAccount'])->findOrFail($id);
        return ResponseHelper::success('Expense/Income entry retrieved successfully', new ExpenseIncomeResource($entry));
    }

    public function store(StoreExpenseIncomeRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $type   = $data['entry_type']; // 'expense' or 'income'
            $amount = (float) $data['amount'];

            // Auto-generate entry number if not supplied
            $entryNo = $data['entry_number'] ?? null;
            if (!$entryNo) {
                $prefix = $type === 'expense' ? 'EXP' : 'INC';
                $year   = now()->format('Y');
                $count  = ExpenseIncome::where('entry_type', $type)->whereYear('entry_date', $year)->count() + 1;
                $entryNo = sprintf('%s-%s-%04d', $prefix, $year, $count);
            }

            $entry = ExpenseIncome::create([
                'entry_number'       => $entryNo,
                'entry_date'         => $data['entry_date'],
                'entry_type'         => $type,
                'account_id'         => $data['account_id'],
                'payment_account_id' => $data['payment_account_id'],
                'amount'             => $amount,
                'payment_mode'       => $data['payment_mode'],
                'reference_no'       => $data['reference_no'] ?? null,
                'description'        => $data['description'] ?? null,
                'is_gst_claimed'     => $data['is_gst_claimed'] ?? false,
                'tax_amount'         => (float) ($data['tax_amount'] ?? 0),
                'vendor_gstin'       => $data['vendor_gstin'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            $paymentAccount = Account::findOrFail($data['payment_account_id']);
            $headAccount    = Account::findOrFail($data['account_id']);

            // Post balanced double-entry voucher
            $taxAmount  = (float) ($data['tax_amount'] ?? 0);
            $baseAmount = max(0, $amount - $taxAmount);
            $entries    = [];

            if ($type === 'expense') {
                // Debit Expense Head
                $entries[] = [
                    'account_id' => $headAccount->id,
                    'entry_type' => 'debit',
                    'amount'     => $baseAmount > 0 ? $baseAmount : $amount,
                    'narration'  => $data['description'] ?? 'Expense: ' . $headAccount->account_name,
                ];

                // Debit GST Input if claimed
                if (!empty($data['is_gst_claimed']) && $taxAmount > 0) {
                    $cgstIn = Account::where('account_code', 'GST-CGST-IN')->first();
                    $sgstIn = Account::where('account_code', 'GST-SGST-IN')->first();
                    $halfTax = round($taxAmount / 2, 2);

                    if ($cgstIn && $sgstIn) {
                        $entries[] = [
                            'account_id' => $cgstIn->id,
                            'entry_type' => 'debit',
                            'amount'     => $halfTax,
                            'narration'  => 'CGST ITC claimed for ' . $entryNo,
                        ];
                        $entries[] = [
                            'account_id' => $sgstIn->id,
                            'entry_type' => 'debit',
                            'amount'     => round($taxAmount - $halfTax, 2),
                            'narration'  => 'SGST ITC claimed for ' . $entryNo,
                        ];
                    }
                }

                // Credit Payment Account (Cash/Bank)
                $entries[] = [
                    'account_id' => $paymentAccount->id,
                    'entry_type' => 'credit',
                    'amount'     => $amount,
                    'narration'  => 'Paid via ' . $data['payment_mode'],
                ];
            } else {
                // Income: Debit Cash/Bank
                $entries[] = [
                    'account_id' => $paymentAccount->id,
                    'entry_type' => 'debit',
                    'amount'     => $amount,
                    'narration'  => 'Received via ' . $data['payment_mode'],
                ];
                // Credit Income Head
                $entries[] = [
                    'account_id' => $headAccount->id,
                    'entry_type' => 'credit',
                    'amount'     => $amount,
                    'narration'  => $data['description'] ?? 'Income: ' . $headAccount->account_name,
                ];
            }

            if (!empty($entries)) {
                $this->ledgerService->postVoucher(
                    voucherType: $type === 'expense' ? 'expense' : 'income',
                    referenceType: 'expense_income',
                    referenceId: $entry->id,
                    entries: $entries,
                    narration: $data['description'] ?? ($type === 'expense' ? 'Operating Expense' : 'Operating Income'),
                    voucherDate: $data['entry_date']
                );
            }

            return ResponseHelper::success(
                ucfirst($type) . ' entry recorded successfully',
                new ExpenseIncomeResource($entry->fresh(['account', 'paymentAccount'])),
                201
            );
        });
    }

    public function destroy($id)
    {
        $entry = ExpenseIncome::findOrFail($id);

        return DB::transaction(function () use ($entry) {
            $paymentAccount = Account::find($entry->payment_account_id);
            $headAccount    = Account::find($entry->account_id);

            // Revert balances
            if ($paymentAccount && $headAccount) {
                if ($entry->entry_type === 'expense') {
                    $paymentAccount->increment('current_balance', $entry->amount);
                    $headAccount->decrement('current_balance', $entry->amount);
                } else {
                    $paymentAccount->decrement('current_balance', $entry->amount);
                    $headAccount->decrement('current_balance', $entry->amount);
                }
            }

            $entry->delete();
            return ResponseHelper::success('Entry deleted and balances reverted successfully', null);
        });
    }
}
