<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use App\Http\Resources\PartyResource;
use App\Models\Party;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Party::query();

        if ($request->filled('type')) {
            $query->whereIn('party_type', [$request->type, 'both']);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%");
            });
        }

        $parties = $query->orderBy('name')->paginate(30);
        return ResponseHelper::success('Parties retrieved successfully', PartyResource::collection($parties));
    }

    public function show($id)
    {
        $party = Party::with(['sales', 'purchases', 'payments'])->findOrFail($id);
        return ResponseHelper::success('Party details retrieved', new PartyResource($party));
    }

    public function store(StorePartyRequest $request)
    {
        $data = $request->validated();
        if (empty($data['name']) && !empty($data['party_name'])) {
            $data['name'] = $data['party_name'];
        }
        unset($data['party_name']);

        if (isset($data['opening_balance']) && !isset($data['current_balance'])) {
            $data['current_balance'] = $data['opening_balance'];
        }
        if (isset($data['metal_opening_balance_grams']) && !isset($data['metal_current_balance_grams'])) {
            $data['metal_current_balance_grams'] = $data['metal_opening_balance_grams'];
        }

        $party = Party::create($data);
        return ResponseHelper::success('Party created successfully', new PartyResource($party), 201);
    }

    public function update(UpdatePartyRequest $request, $id)
    {
        $party = Party::findOrFail($id);
        $party->update($request->validated());

        return ResponseHelper::success('Party updated successfully', new PartyResource($party));
    }

    public function destroy($id)
    {
        $party = Party::findOrFail($id);

        if ($party->sales()->exists() || $party->purchases()->exists()) {
            return ResponseHelper::error('Cannot delete party with existing transaction history.', null, 409);
        }

        $party->delete();
        return ResponseHelper::success('Party deleted successfully');
    }
}
