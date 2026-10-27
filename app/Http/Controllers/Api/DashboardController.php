<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\DailyGoldRate;
use App\Models\ExpenseIncome;
use App\Models\JewelleryCategory;
use App\Models\JewelleryItem;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get aggregated Dashboard & Business KPI Summary for Frontend.
     */
    public function stats(Request $request)
    {
        $today = $request->get('date', now()->format('Y-m-d'));

        // 1. Today's Metal Rates
        $goldRates = DailyGoldRate::with('purity')
            ->where('rate_date', $today)
            ->get();

        if ($goldRates->isEmpty()) {
            $latestDate = DailyGoldRate::max('rate_date');
            if ($latestDate) {
                $goldRates = DailyGoldRate::with('purity')->where('rate_date', $latestDate)->get();
            }
        }

        $ratesFormatted = $goldRates->map(function ($r) {
            return [
                'karatCode'          => $r->purity?->karat_code,
                'karatName'          => $r->purity?->karat_name,
                'metalType'          => $r->metal_type,
                'sellingRatePerGram' => (float) $r->selling_rate_per_gram,
                'buyingRatePerGram'  => (float) $r->buying_rate_per_gram,
                'rateDate'           => $r->rate_date?->format('Y-m-d'),
            ];
        });

        // 2. Today's Transactions
        $todaySales = Sale::whereDate('invoice_date', $today)
            ->where('is_cancelled', false)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total_amount, COALESCE(SUM(total_net_weight), 0) as total_net_weight, COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->first();

        $todayPurchases = Purchase::whereDate('purchase_date', $today)
            ->where('is_cancelled', false)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total_amount, COALESCE(SUM(total_net_weight), 0) as total_net_weight')
            ->first();

        $todayExpenses = ExpenseIncome::whereDate('entry_date', $today)
            ->where('entry_type', 'expense')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        // 3. Cash Drawer & Bank Balances
        $cashAccounts = Account::where('account_type', 'cash')->get();
        $bankAccounts = Account::where('account_type', 'bank')->get();
        $cashBalance  = (float) $cashAccounts->sum('current_balance');
        $bankBalance  = (float) $bankAccounts->sum('current_balance');

        // 4. Inventory Stock Summary
        $inStockStats = JewelleryItem::where('status', 'in_stock')
            ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(gross_weight), 0) as total_gross_weight, COALESCE(SUM(net_gold_weight), 0) as total_net_gold_weight')
            ->first();

        $soldCount = JewelleryItem::where('status', 'sold')->count();

        // Stock by category
        $stockByCategory = JewelleryItem::where('status', 'in_stock')
            ->join('jewellery_categories', 'jewellery_items.category_id', '=', 'jewellery_categories.id')
            ->select('jewellery_categories.category_name', DB::raw('COUNT(jewellery_items.id) as item_count'), DB::raw('SUM(jewellery_items.gross_weight) as total_gross_weight'))
            ->groupBy('jewellery_categories.id', 'jewellery_categories.category_name')
            ->get();

        // 5. Recent Activity
        $recentSales = Sale::where('is_cancelled', false)
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'invoice_number', 'invoice_date', 'customer_name', 'grand_total', 'payment_status', 'total_net_weight']);

        $recentStockEntries = StockEntry::orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'entry_number', 'entry_date', 'entry_type', 'total_items_count', 'total_gross_weight']);

        // 6. Overall Customer & Vendor Counts
        $customerCount = Party::whereIn('party_type', ['customer', 'both'])->count();
        $vendorCount   = Party::whereIn('party_type', ['vendor', 'both'])->count();
        $karigarCount  = Party::where('party_type', 'karigar')->count();

        return ResponseHelper::success('Dashboard statistics retrieved successfully', [
            'asOfDate'         => $today,
            'goldRates'        => $ratesFormatted,
            'todayStats'       => [
                'salesCount'           => (int) $todaySales->count,
                'salesAmount'          => (float) $todaySales->total_amount,
                'salesNetWeightGrams'  => (float) $todaySales->total_net_weight,
                'salesCollectedAmount' => (float) $todaySales->paid_amount,
                'purchasesCount'       => (int) $todayPurchases->count,
                'purchasesAmount'      => (float) $todayPurchases->total_amount,
                'purchasesWeightGrams' => (float) $todayPurchases->total_net_weight,
                'expensesCount'        => (int) $todayExpenses->count,
                'expensesAmount'       => (float) $todayExpenses->total_amount,
            ],
            'cashDrawer'       => [
                'cashInHand'       => $cashBalance,
                'bankBalance'      => $bankBalance,
                'totalLiquidFunds' => $cashBalance + $bankBalance,
            ],
            'inventorySummary' => [
                'inStockItemsCount'  => (int) $inStockStats->total_items,
                'inStockGrossWeight' => (float) $inStockStats->total_gross_weight,
                'inStockNetWeight'   => (float) $inStockStats->total_net_gold_weight,
                'totalSoldItems'     => (int) $soldCount,
                'categoryBreakdown'  => $stockByCategory,
            ],
            'recentSales'        => $recentSales,
            'recentStockEntries' => $recentStockEntries,
            'directoryCounts'    => [
                'totalCustomers' => $customerCount,
                'totalVendors'   => $vendorCount,
                'totalKarigars'  => $karigarCount,
            ],
        ]);
    }
}
