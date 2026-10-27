<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesReturnRequest;
use App\Http\Resources\SalesReturnResource;
use App\Models\Account;
use App\Models\BusinessProfile;
use App\Models\JewelleryItem;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purity;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\AccountingLedgerService;
use App\Services\JewelleryPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function __construct(
        protected JewelleryPricingService $pricingService,
        protected AccountingLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = SalesReturn::with(['sale', 'party', 'items']);

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->sale_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('credit_note_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('credit_note_date', '<=', $request->to_date);
        }

        $returns = $query->orderBy('credit_note_date', 'desc')->paginate(30);
        return ResponseHelper::success('Credit notes retrieved successfully', SalesReturnResource::collection($returns));
    }

    public function show($id)
    {
        $return = SalesReturn::with(['sale', 'party', 'items.purity', 'items.category'])->findOrFail($id);
        return ResponseHelper::success('Credit note details retrieved', new SalesReturnResource($return));
    }

    public function store(StoreSalesReturnRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $sale = Sale::findOrFail($data['sale_id']);
            $profile = BusinessProfile::first();

            // Auto-generate Credit Note number if missing
            $creditNoteNo = $data['credit_note_no'] ?? null;
            if (!$creditNoteNo) {
                $year = now()->format('Y');
                $count = SalesReturn::whereYear('credit_note_date', $year)->count() + 1;
                $creditNoteNo = sprintf('CRN-%s-%04d', $year, $count);
            }

            // Customer details
            $party = null;
            if (!empty($data['party_id'])) {
                $party = Party::find($data['party_id']);
            }
            $customerName   = $party ? $party->party_name : ($sale->customer_name ?? 'Walk-in Customer');
            $customerMobile = $party ? $party->phone : ($sale->customer_mobile ?? null);
            $customerGstin  = $party ? $party->gstin : ($sale->customer_gstin ?? null);

            $isGst = (bool) ($sale->is_gst_applicable ?? true);
            $taxType = $sale->tax_type ?? 'intra_state';
            $shopStateCode = $profile ? (int) $profile->state_code : 19;
            $posStateCode  = $sale->place_of_supply_state_code ?? $shopStateCode;

            // Compute Returned Items
            $totalGrossWeight = 0.000;
            $totalStoneWeight = 0.000;
            $totalNetWeight   = 0.000;
            $totalFineGold    = 0.000;

            $taxableAmount  = 0.00;
            $cgstAmount     = 0.00;
            $sgstAmount     = 0.00;
            $igstAmount     = 0.00;

            $calculatedItems = [];

            foreach ($data['items'] as $itemData) {
                $purity = Purity::findOrFail($itemData['purity_id']);
                $grossWeight = (float) $itemData['gross_weight'];
                $stoneWeight = (float) ($itemData['stone_weight'] ?? 0);
                $netWeight   = max(0, $grossWeight - $stoneWeight);
                $fineGold    = round($netWeight * ($purity->purity_percentage / 100), 3);

                $ratePerGram = (float) $itemData['rate_per_gram'];
                $goldAmt     = round($netWeight * $ratePerGram, 2);
                $makingRef   = (float) ($itemData['making_charges_refunded'] ?? 0);
                $stoneRef    = (float) ($itemData['stone_charges_refunded'] ?? 0);
                $itemTaxable = round($goldAmt + $makingRef + $stoneRef, 2);

                $itemGstRate = (float) ($itemData['gst_rate'] ?? 3.00);
                $itemCgst = 0.00;
                $itemSgst = 0.00;
                $itemIgst = 0.00;

                if ($isGst) {
                    if ($posStateCode === $shopStateCode) {
                        $halfRate = $itemGstRate / 2;
                        $itemCgst = round($itemTaxable * ($halfRate / 100), 2);
                        $itemSgst = round($itemTaxable * ($halfRate / 100), 2);
                    } else {
                        $itemIgst = round($itemTaxable * ($itemGstRate / 100), 2);
                    }
                }

                $itemTotal = round($itemTaxable + $itemCgst + $itemSgst + $itemIgst, 2);

                $totalGrossWeight += $grossWeight;
                $totalStoneWeight += $stoneWeight;
                $totalNetWeight   += $netWeight;
                $totalFineGold    += $fineGold;

                $taxableAmount += $itemTaxable;
                $cgstAmount    += $itemCgst;
                $sgstAmount    += $itemSgst;
                $igstAmount    += $itemIgst;

                $calculatedItems[] = array_merge($itemData, [
                    'purity_percentage' => $purity->purity_percentage,
                    'net_weight'        => $netWeight,
                    'fine_gold_weight'  => $fineGold,
                    'gold_amount'       => $goldAmt,
                    'taxable_amount'    => $itemTaxable,
                    'cgst_amount'       => $itemCgst,
                    'sgst_amount'       => $itemSgst,
                    'igst_amount'       => $itemIgst,
                    'total_amount'      => $itemTotal,
                ]);
            }

            $totalTaxAmount = round($cgstAmount + $sgstAmount + $igstAmount, 2);
            $rawTotal       = $taxableAmount + $totalTaxAmount;
            $roundedTotal   = round($rawTotal);
            $roundOff       = round($roundedTotal - $rawTotal, 2);
            $totalCreditAmount = $roundedTotal;

            // Create Sales Return record
            $salesReturn = SalesReturn::create([
                'credit_note_no'             => $creditNoteNo,
                'credit_note_date'           => $data['credit_note_date'],
                'sale_id'                    => $sale->id,
                'party_id'                   => $data['party_id'] ?? $sale->party_id,
                'customer_name'              => $customerName,
                'customer_mobile'            => $customerMobile,
                'customer_gstin'             => $customerGstin,
                'original_invoice_no'        => $sale->invoice_number ?? $sale->invoice_no,
                'original_invoice_date'      => $sale->invoice_date,
                'place_of_supply_state_id'   => $sale->place_of_supply_state_id,
                'place_of_supply_state_code' => $posStateCode,
                'is_gst_applicable'          => $isGst,
                'tax_type'                   => $taxType,
                'total_gross_weight'         => $totalGrossWeight,
                'total_stone_weight'         => $totalStoneWeight,
                'total_net_weight'           => $totalNetWeight,
                'total_fine_gold_weight'     => $totalFineGold,
                'taxable_amount'             => $taxableAmount,
                'cgst_rate'                  => $isGst && $posStateCode === $shopStateCode ? 1.50 : 0.00,
                'cgst_amount'                => $cgstAmount,
                'sgst_rate'                  => $isGst && $posStateCode === $shopStateCode ? 1.50 : 0.00,
                'sgst_amount'                => $sgstAmount,
                'igst_rate'                  => $isGst && $posStateCode !== $shopStateCode ? 3.00 : 0.00,
                'igst_amount'                => $igstAmount,
                'total_tax_amount'           => $totalTaxAmount,
                'round_off'                  => $roundOff,
                'total_credit_amount'        => $totalCreditAmount,
                'refund_mode'                => $data['refund_mode'] ?? 'store_credit_balance',
                'return_reason'              => $data['return_reason'] ?? 'exchange',
                'notes'                      => $data['notes'] ?? null,
                'created_by'                 => auth()->id(),
            ]);

            // Save items and execute stock inventory actions
            foreach ($calculatedItems as $cItem) {
                SalesReturnItem::create([
                    'sales_return_id'          => $salesReturn->id,
                    'sale_item_id'             => $cItem['sale_item_id'] ?? null,
                    'jewellery_item_id'        => $cItem['jewellery_item_id'] ?? null,
                    'category_id'              => $cItem['category_id'] ?? null,
                    'purity_id'                => $cItem['purity_id'],
                    'item_name'                => $cItem['item_name'],
                    'tag_code'                 => $cItem['tag_code'] ?? null,
                    'hsn_code'                 => '7113',
                    'gross_weight'             => $cItem['gross_weight'],
                    'stone_weight'             => $cItem['stone_weight'] ?? 0,
                    'net_weight'               => $cItem['net_weight'],
                    'fine_gold_weight'         => $cItem['fine_gold_weight'],
                    'rate_per_gram'            => $cItem['rate_per_gram'],
                    'gold_amount'              => $cItem['gold_amount'],
                    'making_charges_refunded'  => $cItem['making_charges_refunded'] ?? 0,
                    'stone_charges_refunded'   => $cItem['stone_charges_refunded'] ?? 0,
                    'taxable_amount'           => $cItem['taxable_amount'],
                    'gst_rate'                 => $cItem['gst_rate'] ?? 3.00,
                    'cgst_amount'              => $cItem['cgst_amount'],
                    'sgst_amount'              => $cItem['sgst_amount'],
                    'igst_amount'              => $cItem['igst_amount'],
                    'total_amount'             => $cItem['total_amount'],
                    'stock_action'             => $cItem['stock_action'] ?? 'restocked_as_original',
                ]);

                // Stock action
                $stockAction = $cItem['stock_action'] ?? 'restocked_as_original';
                if (!empty($cItem['jewellery_item_id'])) {
                    $item = JewelleryItem::find($cItem['jewellery_item_id']);
                    if ($item) {
                        if ($stockAction === 'restocked_as_original') {
                            $item->update(['status' => 'in_stock']);
                        } elseif ($stockAction === 'converted_to_scrap') {
                            $item->update(['status' => 'melted']);
                        } elseif ($stockAction === 'sent_for_repair') {
                            $item->update(['status' => 'under_repair']);
                        }
                    }
                }
            }

            // Refund Settlement Handling
            $refundMode = $data['refund_mode'] ?? 'store_credit_balance';
            $refundPaymentId = null;

            if (in_array($refundMode, ['cash_refund', 'bank_refund'])) {
                $paymentAccId = $data['refund_account_id'] ?? null;
                if (!$paymentAccId) {
                    $fallbackAcc = $refundMode === 'cash_refund'
                        ? Account::where('account_code', 'CASH-01')->first()
                        : Account::where('account_code', 'BANK-SBI-01')->first();
                    $paymentAccId = $fallbackAcc?->id;
                }

                if ($paymentAccId) {
                    $payment = Payment::create([
                        'payment_number' => 'REF-' . strtoupper(substr($refundMode, 0, 4)) . '-' . now()->format('YmdHis'),
                        'payment_date'   => $data['credit_note_date'],
                        'payment_type'   => 'payment',
                        'account_id'     => $paymentAccId,
                        'party_id'       => $data['party_id'] ?? $sale->party_id,
                        'reference_type' => 'sale',
                        'reference_id'   => $sale->id,
                        'payment_mode'   => $refundMode === 'cash_refund' ? 'cash' : 'bank_transfer',
                        'amount'         => $totalCreditAmount,
                        'notes'          => 'Refund for Credit Note: ' . $creditNoteNo,
                        'created_by'     => auth()->id(),
                    ]);

                    $refundPaymentId = $payment->id;
                    $salesReturn->update(['refund_payment_id' => $refundPaymentId]);

                    // Deduct from cash/bank drawer handled uniformly via postVoucher below to prevent duplicate decrement
                    // $payAcc = Account::find($paymentAccId);
                    // if ($payAcc) { $payAcc->decrement('current_balance', $totalCreditAmount); }
                        // $payAcc->decrement('current_balance', $totalCreditAmount);
                    // }
                }
            } elseif ($refundMode === 'store_credit_balance' && !empty($data['party_id'])) {
                // Adjust party balance: Credit customer (reduces receivable or increases store credit)
                $this->ledgerService->adjustPartyBalance($data['party_id'], $totalCreditAmount, 'decrease');
            }

            // Financial Voucher Posting (Double Entry for Sales Return Reversal)
            $salesAccount = Account::where('account_code', 'REV-SALES-01')->first();
            $cgstOutAcc   = Account::where('account_code', 'GST-CGST-OUT')->first();
            $sgstOutAcc   = Account::where('account_code', 'GST-SGST-OUT')->first();
            $igstOutAcc   = Account::where('account_code', 'GST-IGST-OUT')->first();
            $cashAcc      = Account::where('account_code', 'CASH-01')->first();

            $entries = [];

            // Debit Sales Revenue (Reversing Sales)
            if ($salesAccount && $taxableAmount > 0) {
                $entries[] = [
                    'account_id' => $salesAccount->id,
                    'entry_type' => 'debit',
                    'amount'     => $taxableAmount,
                    'narration'  => 'Sales Return Taxable: ' . $creditNoteNo,
                ];
            }

            // Debit GST Output (Reversing Tax Liability)
            if ($cgstOutAcc && $cgstAmount > 0) {
                $entries[] = [
                    'account_id' => $cgstOutAcc->id,
                    'entry_type' => 'debit',
                    'amount'     => $cgstAmount,
                    'narration'  => 'CGST Reversal on Return: ' . $creditNoteNo,
                ];
            }
            if ($sgstOutAcc && $sgstAmount > 0) {
                $entries[] = [
                    'account_id' => $sgstOutAcc->id,
                    'entry_type' => 'debit',
                    'amount'     => $sgstAmount,
                    'narration'  => 'SGST Reversal on Return: ' . $creditNoteNo,
                ];
            }
            if ($igstOutAcc && $igstAmount > 0) {
                $entries[] = [
                    'account_id' => $igstOutAcc->id,
                    'entry_type' => 'debit',
                    'amount'     => $igstAmount,
                    'narration'  => 'IGST Reversal on Return: ' . $creditNoteNo,
                ];
            }

            // Credit Cash / Bank or Customer Account
            $creditAccId = $data['refund_account_id'] ?? ($cashAcc?->id);
            if ($creditAccId && $totalCreditAmount > 0) {
                $entries[] = [
                    'account_id' => $creditAccId,
                    'entry_type' => 'credit',
                    'amount'     => $totalCreditAmount,
                    'narration'  => 'Refund / Store Credit issued: ' . $creditNoteNo,
                ];
            }

            if (!empty($entries)) {
                $this->ledgerService->postVoucher(
                    voucherType: 'sales_journal',
                    referenceType: 'sale',
                    referenceId: $salesReturn->id,
                    entries: $entries,
                    narration: 'Credit Note issued: ' . $creditNoteNo . ' for Inv: ' . $sale->invoice_no,
                    voucherDate: $data['credit_note_date']
                );
            }

            return ResponseHelper::success(
                'Credit note (Sales Return) created successfully',
                new SalesReturnResource($salesReturn->fresh(['sale', 'party', 'items'])),
                201
            );
        });
    }
}
