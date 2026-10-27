<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Account;
use App\Models\Party;
use App\Models\Payment;
use App\Services\AccountingLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected AccountingLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Payment::with(['account', 'party']);

        if ($request->filled('type')) {
            $query->where('payment_type', $request->type);
        }
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('mode')) {
            $query->where('payment_mode', $request->mode);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30);

        return ResponseHelper::success('Payments retrieved successfully', PaymentResource::collection($payments));
    }

    public function show($id)
    {
        $payment = Payment::with(['account', 'party'])->findOrFail($id);
        return ResponseHelper::success('Payment retrieved successfully', new PaymentResource($payment));
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            $type   = $data['payment_type']; // 'receipt' or 'payment'

            // Auto-generate payment number if not supplied
            $paymentNo = $data['payment_number'] ?? null;
            if (!$paymentNo) {
                $prefix = $type === 'receipt' ? 'REC' : 'PAY';
                $year   = now()->format('Y');
                $count  = Payment::where('payment_type', $type)->whereYear('payment_date', $year)->count() + 1;
                $paymentNo = sprintf('%s-%s-%04d', $prefix, $year, $count);
            }

            $payment = Payment::create([
                'payment_number'        => $paymentNo,
                'payment_date'          => $data['payment_date'],
                'payment_type'          => $type,
                'account_id'            => $data['account_id'],
                'party_id'              => $data['party_id'] ?? null,
                'reference_type'        => $data['reference_type'] ?? 'other',
                'reference_id'          => $data['reference_id'] ?? null,
                'payment_mode'          => $data['payment_mode'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'amount'                => $amount,
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => auth()->id(),
            ]);

            $account = Account::findOrFail($data['account_id']);

            // Adjust Party Ledger balance if party is specified
            if (!empty($data['party_id'])) {
                $party = Party::find($data['party_id']);
                if ($party) {
                    if ($type === 'receipt') {
                        // Money received from party -> reduces receivable balance
                        $this->ledgerService->adjustPartyBalance($party->id, $amount, 'decrease');
                    } else {
                        // Money paid to party -> reduces payable balance
                        $this->ledgerService->adjustPartyBalance($party->id, $amount, 'decrease');
                    }
                }
            }

            // Post double-entry financial voucher
            $entries = [];
            $partyAccount = null;
            if (!empty($data['party_id'])) {
                // Determine party default ledger account or Debtors/Creditors
                $party = Party::find($data['party_id']);
                if ($party && $party->party_type === 'vendor') {
                    $partyAccount = Account::where('account_code', 'LIAB-SUNDRY-CREDITORS')->first();
                } else {
                    $partyAccount = Account::where('account_code', 'ASST-SUNDRY-DEBTORS')->first();
                }
            }

            if ($type === 'receipt') {
                // Debit Cash/Bank
                $entries[] = [
                    'account_id' => $account->id,
                    'entry_type' => 'debit',
                    'amount'     => $amount,
                    'narration'  => 'Receipt ' . $paymentNo . ' (' . $data['payment_mode'] . ')',
                ];
                // Credit Party or General Ledger
                if ($partyAccount) {
                    $entries[] = [
                        'account_id' => $partyAccount->id,
                        'entry_type' => 'credit',
                        'amount'     => $amount,
                        'narration'  => 'Received from party: ' . ($payment->party?->party_name ?? ''),
                    ];
                }
            } else {
                // Payment: Debit Party / Creditor
                if ($partyAccount) {
                    $entries[] = [
                        'account_id' => $partyAccount->id,
                        'entry_type' => 'debit',
                        'amount'     => $amount,
                        'narration'  => 'Paid to party: ' . ($payment->party?->party_name ?? ''),
                    ];
                }
                // Credit Cash/Bank
                $entries[] = [
                    'account_id' => $account->id,
                    'entry_type' => 'credit',
                    'amount'     => $amount,
                    'narration'  => 'Payment ' . $paymentNo . ' (' . $data['payment_mode'] . ')',
                ];
            }

            if (!empty($entries)) {
                $this->ledgerService->postVoucher(
                    voucherType: $type === 'receipt' ? 'receipt' : 'payment',
                    referenceType: 'payment',
                    referenceId: $payment->id,
                    entries: $entries,
                    narration: $data['notes'] ?? ($type === 'receipt' ? 'Money Receipt' : 'Payment Made'),
                    voucherDate: $data['payment_date']
                );
            }

            return ResponseHelper::success('Payment recorded successfully', new PaymentResource($payment->fresh(['account', 'party'])), 201);
        });
    }
}
