<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockEntryRequest;
use App\Http\Resources\StockEntryResource;
use App\Models\BusinessProfile;
use App\Models\JewelleryCategory;
use App\Models\JewelleryItem;
use App\Models\Purity;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = StockEntry::with(['artisan', 'items']);
        if ($request->filled('type')) {
            $query->where('entry_type', $request->type);
        }
        $entries = $query->orderBy('entry_date', 'desc')->paginate(30);

        return ResponseHelper::success('Stock entries retrieved successfully', StockEntryResource::collection($entries));
    }

    public function show($id)
    {
        $entry = StockEntry::with(['artisan', 'items'])->findOrFail($id);
        return ResponseHelper::success('Stock entry retrieved', new StockEntryResource($entry));
    }

    public function store(StoreStockEntryRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $profile = BusinessProfile::first();
            $prefix  = $profile->stock_entry_prefix ?? 'STK-';

            $entryNumber = $data['entry_number'] ?? ($prefix . now()->format('Ymd') . '-' . str_pad((string) (StockEntry::count() + 1), 4, '0', STR_PAD_LEFT));

            $totalGross = 0.000;
            $totalStone = 0.000;
            $totalNet   = 0.000;
            $totalFine  = 0.000;
            $totalVal   = 0.00;

            $stockEntry = StockEntry::create([
                'entry_number'              => $entryNumber,
                'entry_date'                => $data['entry_date'],
                'entry_type'                => $data['entry_type'] ?? 'production_inward',
                'artisan_id'                => $data['artisan_id'] ?? null,
                'total_items_count'         => count($data['items']),
                'total_gross_weight'        => 0,
                'total_stone_weight'        => 0,
                'total_net_weight'          => 0,
                'total_fine_gold_weight'    => 0,
                'total_estimated_valuation' => 0,
                'remarks'                   => $data['remarks'] ?? null,
                'created_by'                => auth()->id(),
            ]);

            foreach ($data['items'] as $itemData) {
                $gross = (float) $itemData['gross_weight'];
                $stone = (float) ($itemData['stone_weight'] ?? 0);
                $net   = max(0, $gross - $stone);

                $purity = Purity::findOrFail($itemData['purity_id']);
                $fine   = round($net * ((float) $purity->purity_percentage / 100), 3);

                $rate      = (float) ($itemData['estimated_rate_per_gram'] ?? 0);
                $making    = (float) ($itemData['making_cost'] ?? 0);
                $stoneCost = (float) ($itemData['stone_cost'] ?? 0);
                $itemTotal = round(($net * $rate) + $making + $stoneCost, 2);

                $tagCode = $itemData['tag_code'] ?? ('TAG-' . now()->format('ymd') . '-' . rand(1000, 9999));

                $category = JewelleryCategory::find($itemData['category_id']);
                $hsnCode  = $category ? $category->default_hsn_code : '7113';

                // Automatically add into jewellery showroom stock
                $jewelleryItem = JewelleryItem::create([
                    'tag_code'           => $tagCode,
                    'item_name'          => $itemData['item_name'],
                    'category_id'        => $itemData['category_id'],
                    'purity_id'          => $itemData['purity_id'],
                    'hsn_code'           => $hsnCode,
                    'gross_weight'       => $gross,
                    'stone_weight'       => $stone,
                    'net_gold_weight'    => $net,
                    'fine_gold_weight'   => $fine,
                    'stone_value'        => $stoneCost,
                    'purchase_cost'      => $itemTotal,
                    'huid'               => $itemData['huid'] ?? null,
                    'source_type'        => 'production',
                    'status'             => 'in_stock',
                    'notes'              => $itemData['notes'] ?? 'Inward from in-house workshop',
                ]);

                StockEntryItem::create([
                    'stock_entry_id'          => $stockEntry->id,
                    'jewellery_item_id'       => $jewelleryItem->id,
                    'category_id'             => $itemData['category_id'],
                    'purity_id'               => $itemData['purity_id'],
                    'item_name'               => $itemData['item_name'],
                    'tag_code'                => $tagCode,
                    'huid'                    => $itemData['huid'] ?? null,
                    'gross_weight'            => $gross,
                    'stone_weight'            => $stone,
                    'net_weight'              => $net,
                    'fine_weight'             => $fine,
                    'estimated_rate_per_gram' => $rate,
                    'making_cost'             => $making,
                    'stone_cost'              => $stoneCost,
                    'total_cost'              => $itemTotal,
                    'notes'                   => $itemData['notes'] ?? null,
                ]);

                $totalGross += $gross;
                $totalStone += $stone;
                $totalNet   += $net;
                $totalFine  += $fine;
                $totalVal   += $itemTotal;
            }

            $stockEntry->update([
                'total_gross_weight'        => round($totalGross, 3),
                'total_stone_weight'        => round($totalStone, 3),
                'total_net_weight'          => round($totalNet, 3),
                'total_fine_gold_weight'    => round($totalFine, 3),
                'total_estimated_valuation' => round($totalVal, 2),
            ]);

            return ResponseHelper::success(
                'Production inward stock successfully added to inventory',
                new StockEntryResource($stockEntry->load(['artisan', 'items'])),
                201
            );
        });
    }
}
