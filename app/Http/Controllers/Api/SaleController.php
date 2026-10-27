<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\BusinessProfile;
use App\Models\JewelleryItem;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purity;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOldGoldExchange;
use App\Services\AccountingLedgerService;
use App\Services\JewelleryPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(
        protected JewelleryPricingService $pricingService,
        protected AccountingLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Sale::with(['party', 'items', 'oldGoldExchanges']);

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $sales = $query->orderBy('invoice_date', 'desc')->paginate(30);
        return ResponseHelper::success('Sales invoices retrieved successfully', SaleResource::collection($sales));
    }

    public function show($id)
    {
        $sale = Sale::with(['party', 'items', 'oldGoldExchanges'])->findOrFail($id);
        return ResponseHelper::success('Sales invoice retrieved', new SaleResource($sale));
    }

    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $profile = BusinessProfile::first();
            $prefix  = $profile->invoice_prefix ?? 'INV-';

            $invoiceNo = $data['invoice_number'] ?? ($prefix . now()->format('Ymd') . '-' . str_pad((string) (Sale::count() + 1), 4, '0', STR_PAD_LEFT));

            $shopIsGst = $profile ? (bool) $profile->is_gst_registered : false;
            $isGst     = $data['is_gst_applicable'] ?? $shopIsGst;

            $shopStateCode = $profile ? (int) $profile->state_code : 19;
            $posStateCode  = (int) ($data['place_of_supply_state_code'] ?? $shopStateCode);

            $taxType = $data['tax_type'] ?? 'intra_state';
            if ($isGst) {
                $taxType = ($posStateCode === $shopStateCode) ? 'intra_state' : 'inter_state';
            } else {
                $taxType = 'non_gst';
            }

            $sale = Sale::create([
                'invoice_number'             => $invoiceNo,
                'invoice_date'               => $data['invoice_date'],
                'party_id'                   => $data['party_id'] ?? null,
                'customer_name'              => $data['customer_name'],
                'customer_mobile'            => $data['customer_mobile'] ?? null,
                'customer_email'             => $data['customer_email'] ?? null,
                'customer_address'           => $data['customer_address'] ?? null,
                'customer_gstin'             => $data['customer_gstin'] ?? null,
                'customer_pan'               => $data['customer_pan'] ?? null,
                'place_of_supply_state_id'   => $data['place_of_supply_state_id'] ?? null,
                'place_of_supply_state_code' => $posStateCode,
                'invoice_type'               => $data['invoice_type'] ?? ($isGst ? 'tax_invoice' : 'bill_of_supply'),
                'is_gst_applicable'          => $isGst,
                'tax_type'                   => $taxType,
                'total_gross_weight'         => 0,
                'total_stone_weight'         => 0,
                'total_net_weight'           => 0,
                'total_fine_gold_weight'     => 0,
                'total_gold_amount'          => 0,
                'total_making_charges'       => 0,
                'total_stone_charges'        => 0,
                'hallmarking_charges'        => 0,
                'other_charges'              => 0,
                'discount_amount'            => $data['discount_amount'] ?? 0,
                'taxable_amount'             => 0,
                'cgst_rate'                  => $taxType === 'intra_state' ? 1.50 : 0.00,
                'cgst_amount'                => 0,
                'sgst_rate'                  => $taxType === 'intra_state' ? 1.50 : 0.00,
                'sgst_amount'                => 0,
                'igst_rate'                  => $taxType === 'inter_state' ? 3.00 : 0.00,
                'igst_amount'                => 0,
                'total_tax_amount'           => 0,
                'old_gold_exchange_amount'   => 0,
                'round_off'                  => 0,
                'grand_total'                => 0,
                'paid_amount'                => $data['paid_amount'] ?? 0,
                'balance_amount'             => 0,
                'payment_status'             => 'unpaid',
                'is_cancelled'               => false,
                'notes'                      => $data['notes'] ?? null,
                'created_by'                 => auth()->id(),
            ]);

            $totalGross  = 0.000;
            $totalStone  = 0.000;
            $totalNet    = 0.000;
            $totalFine   = 0.000;
            $totalGold   = 0.00;
            $totalMaking = 0.00;
            $totalStones = 0.00;
            $totalHallmk = 0.00;
            $taxableSum  = 0.00;
            $cgstSum     = 0.00;
            $sgstSum     = 0.00;
            $igstSum     = 0.00;

            foreach ($data['items'] as $itemData) {
                $purity = Purity::findOrFail($itemData['purity_id']);
                $itemData['purity_percentage'] = (float) $purity->purity_percentage;

                $computed = $this->pricingService->computeItemPricing(
                    $itemData,
                    $posStateCode,
                    $isGst
                );

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'jewellery_item_id'  => $itemData['jewellery_item_id'] ?? null,
                    'category_id'        => $itemData['category_id'] ?? null,
                    'purity_id'          => $itemData['purity_id'],
                    'item_name'          => $itemData['item_name'],
                    'tag_code'           => $itemData['tag_code'] ?? null,
                    'huid'               => $itemData['huid'] ?? null,
                    'hsn_code'           => $itemData['hsn_code'] ?? '7113',
                    'gross_weight'       => $computed['gross_weight'],
                    'stone_weight'       => $computed['stone_weight'],
                    'net_weight'         => $computed['net_weight'],
                    'fine_gold_weight'   => $computed['fine_gold_weight'],
                    'gold_rate_per_gram' => $computed['rate_per_gram'],
                    'gold_amount'        => $computed['gold_amount'],
                    'making_charge_type' => $computed['making_charge_type'],
                    'making_charge_rate' => $computed['making_charge_rate'],
                    'making_charges'     => $computed['making_charges'],
                    'stone_charges'      => $computed['stone_charges'],
                    'hallmarking_charge' => $computed['hallmarking_charge'],
                    'discount_amount'    => $computed['discount_amount'],
                    'taxable_amount'     => $computed['taxable_amount'],
                    'gst_rate'           => $computed['gst_rate'],
                    'cgst_amount'        => $computed['cgst_amount'],
                    'sgst_amount'        => $computed['sgst_amount'],
                    'igst_amount'        => $computed['igst_amount'],
                    'total_amount'       => $computed['total_amount'],
                ]);

                // Deplete inventory item
                if (!empty($itemData['jewellery_item_id'])) {
                    $invItem = JewelleryItem::find($itemData['jewellery_item_id']);
                    if ($invItem) {
                        $invItem->update([
                            'status'    => 'sold',
                            'sold_date' => $data['invoice_date'],
                        ]);
                    }
                }

                $totalGross  += $computed['gross_weight'];
                $totalStone  += $computed['stone_weight'];
                $totalNet    += $computed['net_weight'];
                $totalFine   += $computed['fine_gold_weight'];
                $totalGold   += $computed['gold_amount'];
                $totalMaking += $computed['making_charges'];
                $totalStones += $computed['stone_charges'];
                $totalHallmk += $computed['hallmarking_charge'];
                $taxableSum  += $computed['taxable_amount'];
                $cgstSum     += $computed['cgst_amount'];
                $sgstSum     += $computed['sgst_amount'];
                $igstSum     += $computed['igst_amount'];
            }

            // Old Gold Trade-in / Exchange handling
            $oldGoldTotal = 0.00;
            if (!empty($data['old_gold_exchanges'])) {
                foreach ($data['old_gold_exchanges'] as $og) {
                    $ogGross = (float) $og['gross_weight'];
                    $ogDust  = (float) ($og['dust_stone_weight'] ?? 0);
                    $ogNet   = max(0, $ogGross - $ogDust);
                    $ogPct   = (float) $og['purity_percentage'];
                    $ogFine  = round($ogNet * ($ogPct / 100), 3);
                    $ogRate  = (float) $og['buying_rate_per_gram'];
                    $ogVal   = round($ogFine * $ogRate, 2);

                    SaleOldGoldExchange::create([
                        'sale_id'              => $sale->id,
                        'item_description'     => $og['item_description'],
                        'gross_weight'         => round($ogGross, 3),
                        'dust_stone_weight'    => round($ogDust, 3),
                        'net_weight'           => round($ogNet, 3),
                        'purity_percentage'    => round($ogPct, 3),
                        'fine_gold_weight'     => round($ogFine, 3),
                        'buying_rate_per_gram' => round($ogRate, 2),
                        'total_exchange_value' => round($ogVal, 2),
                        'remarks'              => $og['remarks'] ?? null,
                    ]);

                    $oldGoldTotal += $ogVal;
                }
            }

            $discount     = (float) ($data['discount_amount'] ?? 0);
            $totalTax     = $cgstSum + $sgstSum + $igstSum;
            $rawNet       = max(0, ($taxableSum + $totalTax) - $oldGoldTotal);
            $grandTotal   = round($rawNet);
            $roundOff     = round($grandTotal - $rawNet, 2);

            $paidAmount   = min($grandTotal, (float) ($data['paid_amount'] ?? 0));
            $balance      = round($grandTotal - $paidAmount, 2);
            $status       = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partially_paid' : 'unpaid');

            $sale->update([
                'total_gross_weight'       => round($totalGross, 3),
                'total_stone_weight'       => round($totalStone, 3),
                'total_net_weight'         => round($totalNet, 3),
                'total_fine_gold_weight'   => round($totalFine, 3),
                'total_gold_amount'        => round($totalGold, 2),
                'total_making_charges'     => round($totalMaking, 2),
                'total_stone_charges'      => round($totalStones, 2),
                'hallmarking_charges'      => round($totalHallmk, 2),
                'taxable_amount'           => round($taxableSum, 2),
                'cgst_amount'              => round($cgstSum, 2),
                'sgst_amount'              => round($sgstSum, 2),
                'igst_amount'              => round($igstSum, 2),
                'total_tax_amount'         => round($totalTax, 2),
                'old_gold_exchange_amount' => round($oldGoldTotal, 2),
                'round_off'                => $roundOff,
                'grand_total'              => $grandTotal,
                'paid_amount'              => $paidAmount,
                'balance_amount'           => $balance,
                'payment_status'           => $status,
            ]);

            // If customer has a party account and left a balance, update party receivable
            if (!empty($data['party_id']) && $balance > 0) {
                $this->ledgerService->adjustPartyBalance($data['party_id'], $balance, 'increase');
            }

            // Log payment into Cash/Bank Drawer
            if ($paidAmount > 0 && !empty($data['payment_account_id'])) {
                Payment::create([
                    'payment_number'        => 'PAY-SAL-' . now()->format('YmdHis'),
                    'payment_date'          => $data['invoice_date'],
                    'payment_type'          => 'receipt',
                    'account_id'            => $data['payment_account_id'],
                    'party_id'              => $data['party_id'] ?? null,
                    'reference_type'        => 'sale',
                    'reference_id'          => $sale->id,
                    'payment_mode'          => $data['payment_mode'] ?? 'cash',
                    'transaction_reference' => $data['transaction_reference'] ?? null,
                    'amount'                => $paidAmount,
                    'notes'                 => "Receipt for Invoice #{$invoiceNo}",
                    'created_by'            => auth()->id(),
                ]);

                // Update cash/bank balance immediately
                $account = \App\Models\Account::find($data['payment_account_id']);
                if ($account) {
                    $account->increment('current_balance', $paidAmount);
                }
            }

            return ResponseHelper::success('Sale invoice generated successfully', new SaleResource($sale->load(['party', 'items', 'oldGoldExchanges'])), 201);
        });
    }

    public function cancel(Request $request, $id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->is_cancelled) {
            return ResponseHelper::error('Invoice is already cancelled', null, 400);
        }

        $reason = $request->input('reason', 'Cancelled by user');

        return DB::transaction(function () use ($sale, $reason) {
            // Restore sold inventory items
            foreach ($sale->items as $item) {
                if ($item->jewellery_item_id) {
                    $invItem = JewelleryItem::find($item->jewellery_item_id);
                    if ($invItem) {
                        $invItem->update([
                            'status'    => 'in_stock',
                            'sold_date' => null,
                        ]);
                    }
                }
            }

            // Revert party balance if unpaid balance existed
            if ($sale->party_id && $sale->balance_amount > 0) {
                $this->ledgerService->adjustPartyBalance($sale->party_id, $sale->balance_amount, 'decrease');
            }

            $sale->update([
                'is_cancelled'        => true,
                'cancellation_reason' => $reason,
            ]);

            return ResponseHelper::success('Sale invoice cancelled and items restored to stock', new SaleResource($sale->fresh()));
        });
    }
}
