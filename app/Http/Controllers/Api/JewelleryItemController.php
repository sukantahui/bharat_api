<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJewelleryItemRequest;
use App\Http\Requests\UpdateJewelleryItemRequest;
use App\Http\Resources\JewelleryItemResource;
use App\Models\JewelleryItem;
use Illuminate\Http\Request;

class JewelleryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = JewelleryItem::with(['category', 'purity']);

        if ($request->filled('status')) {
            if (strtolower($request->status) !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            // Default to available in stock
            $query->where('status', 'in_stock');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('purity_id')) {
            $query->where('purity_id', $request->purity_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('tag_code', 'like', "%{$search}%")
                  ->orWhere('huid', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 30), 100);
        $items = $query->orderBy('id', 'desc')->paginate($perPage);
        return ResponseHelper::success('Jewellery items retrieved successfully', JewelleryItemResource::collection($items));
    }

    public function show($id)
    {
        $item = JewelleryItem::with(['category', 'purity'])->findOrFail($id);
        return ResponseHelper::success('Jewellery item retrieved', new JewelleryItemResource($item));
    }

    public function byTag($tagCode)
    {
        $item = JewelleryItem::with(['category', 'purity'])->where('tag_code', $tagCode)->first();
        if (!$item) {
            return ResponseHelper::error("Jewellery item with tag [{$tagCode}] not found", null, 404);
        }

        return ResponseHelper::success('Jewellery item found', new JewelleryItemResource($item));
    }

    public function store(StoreJewelleryItemRequest $request)
    {
        $item = JewelleryItem::create($request->validated());
        return ResponseHelper::success('Jewellery item registered in inventory', new JewelleryItemResource($item->load(['category', 'purity'])), 201);
    }

    public function update(UpdateJewelleryItemRequest $request, $id)
    {
        $item = JewelleryItem::findOrFail($id);
        $item->update($request->validated());

        return ResponseHelper::success('Jewellery item updated', new JewelleryItemResource($item->load(['category', 'purity'])));
    }

    public function destroy($id)
    {
        $item = JewelleryItem::findOrFail($id);

        if ($item->status === 'sold') {
            return ResponseHelper::error('Cannot delete an item that has already been sold.', null, 409);
        }

        $item->delete();
        return ResponseHelper::success('Jewellery item removed from inventory');
    }
}
