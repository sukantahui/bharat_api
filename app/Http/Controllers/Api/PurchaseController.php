<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\BusinessProfile;
use App\Models\JewelleryItem;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purity;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\AccountingLedgerService;
use App\Services\JewelleryPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(
        protected JewelleryPricingService $pricingService,
        protected AccountingLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Purchase::with(['party', 'items']);
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }
        if ($request->filled('type')) {
            $query->where('purchase_type', $request->type);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->paginate(30);
        return ResponseHelper::success('Purchases retrieved successfully', PurchaseResource::collection($purchases));
    }

    public function show($id)
    {
        $purchase = Purchase::with(['party', 'items'])->findOrFail($id);
        return ResponseHelper::success('Purchase details retrieved', new PurchaseResource($purchase));
    }

    public function store(StorePurchaseRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $profile = BusinessProfile::first();
            $prefix  = $profile->purchase_prefix ?? 'PUR-';

            $invoiceNo = $data['purchase_invoice_no'] ?? ($prefix . now()->format('Ymd') . '-' . str_pad((string) (Purchase::count() + 1), 4, '0', STR_PAD_LEFT));

            $party = Party::findOrFail($data['party_id']);
            $isGst = $data['is_gst_applicable'] ?? ($profile ? $profile->is_gst_registered : false);

            $taxType = $data['tax_type'] ?? 'intra_state';
            if ($isGst && $party->state_code && $profile->state_code) {
                $taxType = ((int)$party->state_code === (int)$profile->state_code) ? 'intra_state' : 'inter_state';
            }

            $totalGross = 0.000;
            $totalNet   = 0.000;
            $totalFine  = 0.000;
            $totalGold  = 0.00;
            $totalMaking= 0.00;
            $totalStone = 0.00;
            $taxable    = 0.00;
            $cgstTotal  = 0.00;
            $sgstTotal  = 0.00;
            $igstTotal  = 0.00;

            $purchase = Purchase::create([
                'purchase_invoice_no'    => $invoiceNo,
                'purchase_date'          => $data['purchase_date'],
                'vendor_bill_no'         => $data['vendor_bill_no'] ?? null,
                'vendor_bill_date'       => $data['vendor_bill_date'] ?? null,
                'party_id'               => $data['party_id'],
                'purchase_type'          => $data['purchase_type'] ?? 'vendor_jewellery',
                'is_gst_applicable'      => $isGst,
                'tax_type'               => $taxType,
                'total_gross_weight'     => 0,
                'total_net_weight'       => 0,
                'total_fine_gold_weight' => 0,
                'total_gold_amount'      => 0,
                'total_making_charges'   => 0,
                'total_stone_charges'    => 0,
                'other_charges'          => $data['other_charges'] ?? 0,
                'discount_amount'        => $data['discount_amount'] ?? 0,
                'taxable_amount'         => 0,
                'cgst_rate'              => $taxType === 'intra_state' ? 1.50 : 0.00,
                'cgst_amount'            => 0,
                'sgst_rate'              => $taxType === 'intra_state' ? 1.50 : 0.00,
                'sgst_amount'            => 0,
                'igst_rate'              => $taxType === 'inter_state' ? 3.00 : 0.00,
                'igst_amount'            => 0,
                'total_tax_amount'       => 0,
                'round_off'              => 0,
                'grand_total'            => 0,
                'paid_amount'            => $data['paid_amount'] ?? 0,
                'balance_amount'         => 0,
                'payment_status'         => 'unpaid',
                'itc_eligibility'        => $data['itc_eligibility'] ?? 'inputs',
                'notes'                  => $data['notes'] ?? null,
                'created_by'             => auth()->id(),
            ]);

            foreach ($data['items'] as $itemData) {
                $purity = Purity::findOrFail($itemData['purity_id']);
                $itemData['purity_percentage'] = (float) $purity->purity_percentage;

                $computed = $this->pricingService->computeItemPricing(
                    $itemData,
                    $party->state_code,
                    $isGst
                );

                $jewelleryItemId = null;
                if (!empty($itemData['create_stock_item'])) {
                    $tagCode = $itemData['tag_code'] ?? ('TAG-' . now()->format('ymd') . '-' . rand(1000, 9999));
                    $jewelleryItem = JewelleryItem::create([
                        'tag_code'         => $tagCode,
                        'item_name'        => $itemData['item_description'],
                        'category_id'      => $itemData['category_id'] ?? 1,
                        'purity_id'        => $itemData['purity_id'],
                        'hsn_code'         => $itemData['hsn_code'] ?? '7113',
                        'gross_weight'     => $computed['gross_weight'],
                        'stone_weight'     => $computed['stone_weight'],
                        'net_gold_weight'  => $computed['net_weight'],
                        'fine_gold_weight' => $computed['fine_gold_weight'],
                        'stone_value'      => $computed['stone_charges'],
                        'purchase_cost'    => $computed['total_amount'],
                        'source_type'      => 'purchase',
                        'status'           => 'in_stock',
                    ]);
                    $jewelleryItemId = $jewelleryItem->id;
                }

                PurchaseItem::create([
                    'purchase_id'       => $purchase->id,
                    'jewellery_item_id' => $jewelleryItemId,
                    'category_id'       => $itemData['category_id'] ?? null,
                    'purity_id'         => $itemData['purity_id'],
                    'item_description'  => $itemData['item_description'],
                    'hsn_code'          => $itemData['hsn_code'] ?? '7113',
                    'gross_weight'      => $computed['gross_weight'],
                    'stone_weight'      => $computed['stone_weight'],
                    'net_weight'        => $computed['net_weight'],
                    'purity_percentage' => $computed['purity_percentage'],
                    'fine_gold_weight'  => $computed['fine_gold_weight'],
                    'rate_per_gram'     => $computed['rate_per_gram'],
                    'gold_amount'       => $computed['gold_amount'],
                    'making_charges'    => $computed['making_charges'],
                    'stone_charges'     => $computed['stone_charges'],
                    'taxable_amount'    => $computed['taxable_amount'],
                    'gst_rate'          => $computed['gst_rate'],
                    'cgst_amount'       => $computed['cgst_amount'],
                    'sgst_amount'       => $computed['sgst_amount'],
                    'igst_amount'       => $computed['igst_amount'],
                    'total_amount'      => $computed['total_amount'],
                ]);

                $totalGross  += $computed['gross_weight'];
                $totalNet    += $computed['net_weight'];
                $totalFine   += $computed['fine_gold_weight'];
                $totalGold   += $computed['gold_amount'];
                $totalMaking += $computed['making_charges'];
                $totalStone  += $computed['stone_charges'];
                $taxable     += $computed['taxable_amount'];
                $cgstTotal   += $computed['cgst_amount'];
                $sgstTotal   += $computed['sgst_amount'];
                $igstTotal   += $computed['igst_amount'];
            }

            $otherCharges = (float) ($data['other_charges'] ?? 0);
            $discount     = (float) ($data['discount_amount'] ?? 0);
            $totalTax     = $cgstTotal + $sgstTotal + $igstTotal;
            $rawGrand     = ($taxable + $totalTax + $otherCharges) - $discount;
            $grandTotal   = round($rawGrand);
            $roundOff     = round($grandTotal - $rawGrand, 2);

            $paidAmount = min($grandTotal, (float) ($data['paid_amount'] ?? 0));
            $balance    = round($grandTotal - $paidAmount, 2);
            $status     = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partially_paid' : 'unpaid');

            $purchase->update([
                'total_gross_weight'     => round($totalGross, 3),
                'total_net_weight'       => round($totalNet, 3),
                'total_fine_gold_weight' => round($totalFine, 3),
                'total_gold_amount'      => round($totalGold, 2),
                'total_making_charges'   => round($totalMaking, 2),
                'total_stone_charges'    => round($totalStone, 2),
                'taxable_amount'         => round($taxable, 2),
                'cgst_amount'            => round($cgstTotal, 2),
                'sgst_amount'            => round($sgstTotal, 2),
                'igst_amount'            => round($igstTotal, 2),
                'total_tax_amount'       => round($totalTax, 2),
                'round_off'              => $roundOff,
                'grand_total'            => $grandTotal,
                'paid_amount'            => $paidAmount,
                'balance_amount'         => $balance,
                'payment_status'         => $status,
            ]);

            // Update vendor payable balance
            if ($balance > 0) {
                $this->ledgerService->adjustPartyBalance($party->id, $balance, 'increase');
            }

            // If payment made, log payment entry
            if ($paidAmount > 0 && !empty($data['payment_account_id'])) {
                Payment::create([
                    'payment_number' => 'PAY-PUR-' . now()->format('YmdHis'),
                    'payment_date'   => $data['purchase_date'],
                    'payment_type'   => 'payment',
                    'account_id'     => $data['payment_account_id'],
                    'party_id'       => $party->id,
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'payment_mode'   => $data['payment_mode'] ?? 'bank_transfer',
                    'amount'         => $paidAmount,
                    'notes'          => "Payment for Purchase #{$invoiceNo}",
                    'created_by'     => auth()->id(),
                ]);
            }

            return ResponseHelper::success('Purchase recorded successfully', new PurchaseResource($purchase->load(['party', 'items'])), 201);
        });
    }

    public function cancel(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        if ($purchase->is_cancelled) {
            return ResponseHelper::error('Purchase invoice is already cancelled', null, 400);
        }

        $reason = $request->input('reason', 'Cancelled by user');

        return DB::transaction(function () use ($purchase, $reason) {
            // Check if any items generated inventory that was already sold
            foreach ($purchase->items as $item) {
                if ($item->jewellery_item_id) {
                    $invItem = JewelleryItem::find($item->jewellery_item_id);
                    if ($invItem && $invItem->status === 'sold') {
                        return ResponseHelper::error("Cannot cancel purchase: item [{$invItem->tag_code}] has already been sold.", null, 409);
                    }
                    if ($invItem) {
                        $invItem->delete();
                    }
                }
            }

            // Revert vendor payable balance if unpaid balance existed
            if ($purchase->party_id && $purchase->balance_amount > 0) {
                $this->ledgerService->adjustPartyBalance($purchase->party_id, $purchase->balance_amount, 'decrease');
            }

            $purchase->update([
                'is_cancelled'        => true,
                'cancellation_reason' => $reason,
            ]);

            return ResponseHelper::success('Purchase cancelled successfully and inventory adjusted', new PurchaseResource($purchase->fresh(['party', 'items'])));
        });
    }
}
