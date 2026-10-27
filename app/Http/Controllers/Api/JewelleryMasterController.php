<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyGoldRateRequest;
use App\Http\Resources\DailyGoldRateResource;
use App\Http\Resources\JewelleryCategoryResource;
use App\Http\Resources\PurityResource;
use App\Models\DailyGoldRate;
use App\Models\JewelleryCategory;
use App\Models\Purity;
use Illuminate\Http\Request;

class JewelleryMasterController extends Controller
{
    // -------------------------------------------------------------
    // Purities
    // -------------------------------------------------------------
    public function purities()
    {
        $purities = Purity::where('is_active', true)->orderBy('purity_percentage', 'desc')->get();
        return ResponseHelper::success('Purities retrieved successfully', PurityResource::collection($purities));
    }

    public function storePurity(Request $request)
    {
        $validated = $request->validate([
            'karat_name'        => 'required|string|max:50',
            'karat_code'        => 'required|string|max:20|unique:purities,karat_code',
            'purity_percentage' => 'required|numeric|min:1|max:100',
            'hallmark_code'     => 'nullable|string|max:20',
            'description'       => 'nullable|string|max:255',
        ]);

        $purity = Purity::create($validated);
        return ResponseHelper::success('Purity created successfully', new PurityResource($purity), 201);
    }

    // -------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------
    public function categories()
    {
        $categories = JewelleryCategory::where('is_active', true)->orderBy('category_name')->get();
        return ResponseHelper::success('Categories retrieved successfully', JewelleryCategoryResource::collection($categories));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category_name'    => 'required|string|max:100',
            'category_code'    => 'required|string|max:20|unique:jewellery_categories,category_code',
            'default_hsn_code' => 'sometimes|string|max:10',
            'description'      => 'nullable|string|max:255',
        ]);

        $category = JewelleryCategory::create($validated);
        return ResponseHelper::success('Category created successfully', new JewelleryCategoryResource($category), 201);
    }

    // -------------------------------------------------------------
    // Daily Gold Rates
    // -------------------------------------------------------------
    public function rates(Request $request)
    {
        $query = DailyGoldRate::with('purity');
        if ($request->has('date')) {
            $query->where('rate_date', $request->date);
        }
        $rates = $query->orderBy('rate_date', 'desc')->paginate(30);

        return ResponseHelper::success('Gold rates retrieved successfully', DailyGoldRateResource::collection($rates));
    }

    public function todayRates()
    {
        $today = now()->format('Y-m-d');
        $rates = DailyGoldRate::with('purity')
            ->where('rate_date', $today)
            ->get();

        if ($rates->isEmpty()) {
            // Fallback to most recent recorded rate
            $latestDate = DailyGoldRate::max('rate_date');
            if ($latestDate) {
                $rates = DailyGoldRate::with('purity')->where('rate_date', $latestDate)->get();
            }
        }

        return ResponseHelper::success('Today metal rates retrieved successfully', DailyGoldRateResource::collection($rates));
    }

    public function storeRate(StoreDailyGoldRateRequest $request)
    {
        $data = $request->validated();
        $rate = DailyGoldRate::updateOrCreate(
            [
                'rate_date'  => $data['rate_date'],
                'purity_id'  => $data['purity_id'],
                'metal_type' => $data['metal_type'] ?? 'gold',
            ],
            array_merge($data, ['created_by' => auth()->id()])
        );

        return ResponseHelper::success('Daily metal rate recorded successfully', new DailyGoldRateResource($rate->load('purity')), 201);
    }

    public function dailyRates(Request $request)
    {
        return $this->rates($request);
    }

    public function todayRate()
    {
        return $this->todayRates();
    }

    public function storeDailyRate(StoreDailyGoldRateRequest $request)
    {
        return $this->storeRate($request);
    }
}
