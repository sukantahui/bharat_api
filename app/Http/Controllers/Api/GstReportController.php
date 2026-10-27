<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\ExpenseIncome;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GstReportController extends Controller
{
    /**
     * Check organization GST Registration Status and Configuration.
     */
    public function status()
    {
        $profile = BusinessProfile::with('state')->first();

        return ResponseHelper::success('GST configuration status', [
            'isGstRegistered' => $profile ? (bool) $profile->is_gst_registered : false,
            'gstin'           => $profile?->gstin,
            'businessName'    => $profile?->business_name,
            'stateCode'       => $profile?->state_code,
            'stateName'       => $profile?->state?->state_name,
            'hsnCode'         => $profile?->hsn_code ?? '7113',
            'goldGstRate'     => (float) ($profile?->gold_gst_rate ?? 3.00),
            'silverGstRate'   => (float) ($profile?->silver_gst_rate ?? 3.00),
            'makingGstRate'   => (float) ($profile?->making_gst_rate ?? 5.00),
        ]);
    }

    /**
     * GSTR-1 Monthly / Quarterly Outward Supplies Summary.
     * Table 4 (B2B), Table 5 (B2CL), Table 7 (B2CS), Table 9B (CDNR/CDNUR), Table 12 (HSN).
     */
    public function gstr1Summary(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->get('to_date', now()->format('Y-m-d'));

        $salesQuery = Sale::whereBetween('invoice_date', [$fromDate, $toDate])
            ->where('is_cancelled', false);

        $sales = (clone $salesQuery)->with(['party', 'items'])->get();

        // 1. Table 4: B2B Invoices (Customers with GSTIN)
        $b2bInvoices = [];
        $b2bTaxable  = 0.00;
        $b2bCgst     = 0.00;
        $b2bSgst     = 0.00;
        $b2bIgst     = 0.00;
        $b2bTotal    = 0.00;

        // 2. Table 5: B2C Large (Inter-state invoice > 2.5 Lakhs to unregistered)
        $b2clInvoices = [];
        $b2clTaxable  = 0.00;
        $b2clIgst     = 0.00;

        // 3. Table 7: B2C Small (Retail sales intra-state or inter-state <= 2.5 Lakhs)
        $b2csTaxable = 0.00;
        $b2csCgst    = 0.00;
        $b2csSgst    = 0.00;
        $b2csIgst    = 0.00;

        foreach ($sales as $sale) {
            $gstin = trim($sale->customer_gstin ?? $sale->party?->gstin ?? '');

            if (!empty($gstin) && strlen($gstin) === 15) {
                // Registered B2B
                $b2bInvoices[] = [
                    'invoiceNo'       => $sale->invoice_no,
                    'invoiceDate'     => $sale->invoice_date->format('Y-m-d'),
                    'customerName'    => $sale->customer_name,
                    'customerGstin'   => $gstin,
                    'posStateCode'    => $sale->place_of_supply_state_code,
                    'taxableAmount'   => (float) $sale->taxable_amount,
                    'cgstAmount'      => (float) $sale->cgst_amount,
                    'sgstAmount'      => (float) $sale->sgst_amount,
                    'igstAmount'      => (float) $sale->igst_amount,
                    'totalAmount'     => (float) $sale->net_payable_amount,
                ];
                $b2bTaxable += (float) $sale->taxable_amount;
                $b2bCgst    += (float) $sale->cgst_amount;
                $b2bSgst    += (float) $sale->sgst_amount;
                $b2bIgst    += (float) $sale->igst_amount;
                $b2bTotal   += (float) $sale->net_payable_amount;
            } elseif ($sale->tax_type === 'inter_state' && (float) $sale->net_payable_amount > 250000) {
                // B2CL (Inter-state > 2.5L)
                $b2clInvoices[] = [
                    'invoiceNo'     => $sale->invoice_no,
                    'invoiceDate'   => $sale->invoice_date->format('Y-m-d'),
                    'customerName'  => $sale->customer_name,
                    'posStateCode'  => $sale->place_of_supply_state_code,
                    'taxableAmount' => (float) $sale->taxable_amount,
                    'igstAmount'    => (float) $sale->igst_amount,
                    'totalAmount'   => (float) $sale->net_payable_amount,
                ];
                $b2clTaxable += (float) $sale->taxable_amount;
                $b2clIgst    += (float) $sale->igst_amount;
            } else {
                // B2CS (All other retail)
                $b2csTaxable += (float) $sale->taxable_amount;
                $b2csCgst    += (float) $sale->cgst_amount;
                $b2csSgst    += (float) $sale->sgst_amount;
                $b2csIgst    += (float) $sale->igst_amount;
            }
        }

        // 4. Table 9B: Credit / Debit Notes (Sales Returns)
        $returns = SalesReturn::with('party')
            ->whereBetween('credit_note_date', [$fromDate, $toDate])
            ->get();

        $cdnrList = []; // B2B Credit Notes
        $cdnurList = []; // B2C Credit Notes

        $cdnrTaxable  = 0.00;
        $cdnrCgst     = 0.00;
        $cdnrSgst     = 0.00;
        $cdnrIgst     = 0.00;

        $cdnurTaxable = 0.00;
        $cdnurCgst    = 0.00;
        $cdnurSgst    = 0.00;
        $cdnurIgst    = 0.00;

        foreach ($returns as $ret) {
            $gstin = trim($ret->customer_gstin ?? $ret->party?->gstin ?? '');

            $noteData = [
                'creditNoteNo'        => $ret->credit_note_no,
                'creditNoteDate'      => $ret->credit_note_date->format('Y-m-d'),
                'originalInvoiceNo'   => $ret->original_invoice_no,
                'originalInvoiceDate' => $ret->original_invoice_date->format('Y-m-d'),
                'customerName'        => $ret->customer_name,
                'customerGstin'       => $gstin,
                'posStateCode'        => $ret->place_of_supply_state_code,
                'taxableAmount'       => (float) $ret->taxable_amount,
                'cgstAmount'          => (float) $ret->cgst_amount,
                'sgstAmount'          => (float) $ret->sgst_amount,
                'igstAmount'          => (float) $ret->igst_amount,
                'totalCreditAmount'   => (float) $ret->total_credit_amount,
            ];

            if (!empty($gstin) && strlen($gstin) === 15) {
                $cdnrList[] = $noteData;
                $cdnrTaxable += (float) $ret->taxable_amount;
                $cdnrCgst    += (float) $ret->cgst_amount;
                $cdnrSgst    += (float) $ret->sgst_amount;
                $cdnrIgst    += (float) $ret->igst_amount;
            } else {
                $cdnurList[] = $noteData;
                $cdnurTaxable += (float) $ret->taxable_amount;
                $cdnurCgst    += (float) $ret->cgst_amount;
                $cdnurSgst    += (float) $ret->sgst_amount;
                $cdnurIgst    += (float) $ret->igst_amount;
            }
        }

        // 5. Table 12: HSN Summary (HSN 7113 Gold Jewellery)
        $hsnSummary = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('invoice_date', [$fromDate, $toDate])
              ->where('payment_status', '!=', 'cancelled');
        })
        ->select(
            'hsn_code',
            DB::raw('COUNT(id) as total_items'),
            DB::raw('SUM(gross_weight) as total_gross_weight'),
            DB::raw('SUM(net_weight) as total_net_weight'),
            DB::raw('SUM(taxable_amount) as total_taxable'),
            DB::raw('SUM(cgst_amount) as total_cgst'),
            DB::raw('SUM(sgst_amount) as total_sgst'),
            DB::raw('SUM(igst_amount) as total_igst'),
            DB::raw('SUM(total_amount) as total_value')
        )
        ->groupBy('hsn_code')
        ->get();

        return ResponseHelper::success('GSTR-1 summary generated successfully', [
            'period' => [
                'fromDate' => $fromDate,
                'toDate'   => $toDate,
            ],
            'table4_b2b' => [
                'count'         => count($b2bInvoices),
                'taxableAmount' => round($b2bTaxable, 2),
                'cgstAmount'    => round($b2bCgst, 2),
                'sgstAmount'    => round($b2bSgst, 2),
                'igstAmount'    => round($b2bIgst, 2),
                'totalAmount'   => round($b2bTotal, 2),
                'invoices'      => $b2bInvoices,
            ],
            'table5_b2cl' => [
                'count'         => count($b2clInvoices),
                'taxableAmount' => round($b2clTaxable, 2),
                'igstAmount'    => round($b2clIgst, 2),
                'invoices'      => $b2clInvoices,
            ],
            'table7_b2cs' => [
                'taxableAmount' => round($b2csTaxable, 2),
                'cgstAmount'    => round($b2csCgst, 2),
                'sgstAmount'    => round($b2csSgst, 2),
                'igstAmount'    => round($b2csIgst, 2),
                'totalTax'      => round($b2csCgst + $b2csSgst + $b2csIgst, 2),
            ],
            'table9b_cdnr' => [
                'count'         => count($cdnrList),
                'taxableAmount' => round($cdnrTaxable, 2),
                'cgstAmount'    => round($cdnrCgst, 2),
                'sgstAmount'    => round($cdnrSgst, 2),
                'igstAmount'    => round($cdnrIgst, 2),
                'notes'         => $cdnrList,
            ],
            'table9b_cdnur' => [
                'count'         => count($cdnurList),
                'taxableAmount' => round($cdnurTaxable, 2),
                'cgstAmount'    => round($cdnurCgst, 2),
                'sgstAmount'    => round($cdnurSgst, 2),
                'igstAmount'    => round($cdnurIgst, 2),
                'notes'         => $cdnurList,
            ],
            'table12_hsn' => $hsnSummary->map(fn ($h) => [
                'hsnCode'          => $h->hsn_code ?? '7113',
                'description'      => 'Articles of jewellery of precious metal',
                'uqc'              => 'GMS',
                'totalItems'       => (int) $h->total_items,
                'totalGrossWeight' => (float) $h->total_gross_weight,
                'totalNetWeight'   => (float) $h->total_net_weight,
                'taxableAmount'    => (float) $h->total_taxable,
                'cgstAmount'       => (float) $h->total_cgst,
                'sgstAmount'       => (float) $h->total_sgst,
                'igstAmount'       => (float) $h->total_igst,
                'totalValue'       => (float) $h->total_value,
            ]),
        ]);
    }

    /**
     * GSTR-3B Monthly Tax Liability & Input Tax Credit (ITC) Summary.
     */
    public function gstr3bSummary(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->get('to_date', now()->format('Y-m-d'));

        // 1. Table 3.1: Outward Supplies (Sales minus Sales Returns)
        $grossSales = Sale::whereBetween('invoice_date', [$fromDate, $toDate])
            ->where('is_cancelled', false)
            ->selectRaw('
                COALESCE(SUM(taxable_amount), 0) as taxable,
                COALESCE(SUM(cgst_amount), 0) as cgst,
                COALESCE(SUM(sgst_amount), 0) as sgst,
                COALESCE(SUM(igst_amount), 0) as igst
            ')->first();

        $salesReturns = SalesReturn::whereBetween('credit_note_date', [$fromDate, $toDate])
            ->selectRaw('
                COALESCE(SUM(taxable_amount), 0) as taxable,
                COALESCE(SUM(cgst_amount), 0) as cgst,
                COALESCE(SUM(sgst_amount), 0) as sgst,
                COALESCE(SUM(igst_amount), 0) as igst
            ')->first();

        $netOutwardTaxable = max(0, (float) $grossSales->taxable - (float) $salesReturns->taxable);
        $netOutwardCgst    = max(0, (float) $grossSales->cgst - (float) $salesReturns->cgst);
        $netOutwardSgst    = max(0, (float) $grossSales->sgst - (float) $salesReturns->sgst);
        $netOutwardIgst    = max(0, (float) $grossSales->igst - (float) $salesReturns->igst);
        $totalOutwardTax   = $netOutwardCgst + $netOutwardSgst + $netOutwardIgst;

        // 2. Table 4: Eligible ITC (Purchases from GST registered vendors + GST business expenses minus debit notes)
        $purchasesItc = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])
            ->where('is_gst_applicable', true)
            ->where('is_cancelled', false)
            ->selectRaw('
                COALESCE(SUM(taxable_amount), 0) as taxable,
                COALESCE(SUM(cgst_amount), 0) as cgst,
                COALESCE(SUM(sgst_amount), 0) as sgst,
                COALESCE(SUM(igst_amount), 0) as igst
            ')->first();

        $expenseItc = ExpenseIncome::whereBetween('entry_date', [$fromDate, $toDate])
            ->where('entry_type', 'expense')
            ->where('is_gst_claimed', true)
            ->sum('tax_amount');

        $halfExpenseItc = round((float) $expenseItc / 2, 2);

        $purchaseReturns = PurchaseReturn::whereBetween('debit_note_date', [$fromDate, $toDate])
            ->selectRaw('
                COALESCE(SUM(taxable_amount), 0) as taxable,
                COALESCE(SUM(cgst_amount), 0) as cgst,
                COALESCE(SUM(sgst_amount), 0) as sgst,
                COALESCE(SUM(igst_amount), 0) as igst
            ')->first();

        $grossItcCgst = (float) $purchasesItc->cgst + $halfExpenseItc;
        $grossItcSgst = (float) $purchasesItc->sgst + round((float) $expenseItc - $halfExpenseItc, 2);
        $grossItcIgst = (float) $purchasesItc->igst;

        $netItcCgst = max(0, $grossItcCgst - (float) $purchaseReturns->cgst);
        $netItcSgst = max(0, $grossItcSgst - (float) $purchaseReturns->sgst);
        $netItcIgst = max(0, $grossItcIgst - (float) $purchaseReturns->igst);
        $totalItc   = $netItcCgst + $netItcSgst + $netItcIgst;

        // 3. Net Tax Payable (Outward Tax Liability minus ITC)
        $payableCgst = max(0, $netOutwardCgst - $netItcCgst);
        $payableSgst = max(0, $netOutwardSgst - $netItcSgst);
        $payableIgst = max(0, $netOutwardIgst - $netItcIgst);
        $totalCashPayable = $payableCgst + $payableSgst + $payableIgst;

        return ResponseHelper::success('GSTR-3B summary generated successfully', [
            'period' => [
                'fromDate' => $fromDate,
                'toDate'   => $toDate,
            ],
            'table3_1_outwardSupplies' => [
                'netTaxableAmount' => round($netOutwardTaxable, 2),
                'cgstAmount'       => round($netOutwardCgst, 2),
                'sgstAmount'       => round($netOutwardSgst, 2),
                'igstAmount'       => round($netOutwardIgst, 2),
                'totalTax'         => round($totalOutwardTax, 2),
            ],
            'table4_eligibleItc' => [
                'cgstItc'  => round($netItcCgst, 2),
                'sgstItc'  => round($netItcSgst, 2),
                'igstItc'  => round($netItcIgst, 2),
                'totalItc' => round($totalItc, 2),
            ],
            'netTaxPayableInCash' => [
                'cgst'     => round($payableCgst, 2),
                'sgst'     => round($payableSgst, 2),
                'igst'     => round($payableIgst, 2),
                'totalTax' => round($totalCashPayable, 2),
            ],
        ]);
    }
}
