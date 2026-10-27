<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\FinancialVoucherEntry;
use App\Models\Payment;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * List all chart of accounts.
     */
    public function index(Request $request)
    {
        $query = Account::with('accountGroup');

        if ($request->filled('type')) {
            $query->whereHas('accountGroup', function ($q) use ($request) {
                $q->where('primary_type', $request->type);
            });
        }

        if ($request->filled('is_cash')) {
            $query->where('account_type', 'cash');
        }

        if ($request->filled('is_bank')) {
            $query->where('account_type', 'bank');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhere('account_code', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('account_code')->get();

        return ResponseHelper::success('Accounts retrieved successfully', AccountResource::collection($accounts));
    }

    /**
     * Show account details.
     */
    public function show($id)
    {
        $account = Account::with('accountGroup')->findOrFail($id);
        return ResponseHelper::success('Account retrieved successfully', new AccountResource($account));
    }

    /**
     * Real-time Cash-in-Hand and Bank Drawer Summary for Jewellery Shop.
     */
    public function cashDrawer(Request $request)
    {
        $today = $request->get('date', now()->format('Y-m-d'));

        $cashAccounts = Account::where('account_type', 'cash')->get();
        $bankAccounts = Account::where('account_type', 'bank')->get();

        $totalCashBalance = $cashAccounts->sum('current_balance');
        $totalBankBalance = $bankAccounts->sum('current_balance');
        $totalLiquidFunds = $totalCashBalance + $totalBankBalance;

        // Cash flow transactions today
        $cashAccIds = $cashAccounts->pluck('id')->toArray();

        $todayReceipts = Payment::whereIn('account_id', $cashAccIds)
            ->where('payment_type', 'receipt')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $todayPayments = Payment::whereIn('account_id', $cashAccIds)
            ->where('payment_type', 'payment')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $netCashMovement = $todayReceipts - $todayPayments;

        return ResponseHelper::success('Cash drawer summary retrieved', [
            'asOfDate'          => $today,
            'totalCashBalance'  => (float) $totalCashBalance,
            'totalBankBalance'  => (float) $totalBankBalance,
            'totalLiquidFunds'  => (float) $totalLiquidFunds,
            'todayCashInflow'   => (float) $todayReceipts,
            'todayCashOutflow'  => (float) $todayPayments,
            'netCashMovement'   => (float) $netCashMovement,
            'cashAccounts'      => AccountResource::collection($cashAccounts),
            'bankAccounts'      => AccountResource::collection($bankAccounts),
        ]);
    }

    /**
     * Account Statement / Ledger Entries.
     */
    public function statement($id, Request $request)
    {
        $account = Account::with('accountGroup')->findOrFail($id);

        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->get('to_date', now()->format('Y-m-d'));

        $entries = FinancialVoucherEntry::with('financialVoucher')
            ->where('account_id', $id)
            ->whereHas('financialVoucher', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('voucher_date', [$fromDate, $toDate]);
            })
            ->join('financial_vouchers', 'financial_voucher_entries.financial_voucher_id', '=', 'financial_vouchers.id')
            ->orderBy('financial_vouchers.voucher_date', 'asc')
            ->orderBy('financial_voucher_entries.id', 'asc')
            ->select('financial_voucher_entries.*')
            ->paginate(50);

        $totalDebit = FinancialVoucherEntry::where('account_id', $id)
            ->where('entry_type', 'debit')
            ->whereHas('financialVoucher', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('voucher_date', [$fromDate, $toDate]);
            })->sum('amount');

        $totalCredit = FinancialVoucherEntry::where('account_id', $id)
            ->where('entry_type', 'credit')
            ->whereHas('financialVoucher', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('voucher_date', [$fromDate, $toDate]);
            })->sum('amount');

        return ResponseHelper::success('Account statement retrieved', [
            'account'        => new AccountResource($account),
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'currentBalance' => (float) $account->current_balance,
            'periodDebit'    => (float) $totalDebit,
            'periodCredit'   => (float) $totalCredit,
            'entries'        => $entries,
        ]);
    }
}
