<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PosTerminalResource;
use App\Models\PosTerminal;
use Illuminate\Http\Request;

class PosTerminalController extends Controller
{
    /**
     * Display a listing of POS terminals/counters.
     */
    public function index(Request $request)
    {
        $query = PosTerminal::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('area')) {
            $query->where('area', 'like', '%' . $request->area . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%")
                  ->orWhere('assigned_staff', 'like', "%{$search}%");
            });
        }

        $terminals = $query->orderBy('id', 'asc')->get();

        return ResponseHelper::success('POS Terminals retrieved successfully', PosTerminalResource::collection($terminals));
    }

    /**
     * Store a newly created POS terminal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:50|unique:pos_terminals,code',
            'name'           => 'required|string|max:255',
            'counter_type'   => 'nullable|string|max:50',
            'counterType'    => 'nullable|string|max:50',
            'area'           => 'required|string|max:255',
            'branch_code'    => 'nullable|string|max:50',
            'branchCode'     => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'invoice_prefix' => 'nullable|string|max:50',
            'invoicePrefix'  => 'nullable|string|max:50',
            'assigned_staff' => 'nullable|string|max:255',
            'assignedStaff'  => 'nullable|string|max:255',
            'opening_balance'=> 'nullable|numeric|min:0',
            'openingBalance' => 'nullable|numeric|min:0',
            'status'         => 'nullable|string|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        $terminal = PosTerminal::create([
            'code'           => strtoupper(trim($validated['code'])),
            'name'           => trim($validated['name']),
            'counter_type'   => $validated['counter_type'] ?? $validated['counterType'] ?? 'gold',
            'area'           => trim($validated['area']),
            'branch_code'    => $validated['branch_code'] ?? $validated['branchCode'] ?? null,
            'address'        => $validated['address'] ?? null,
            'invoice_prefix' => strtoupper($validated['invoice_prefix'] ?? $validated['invoicePrefix'] ?? 'INV'),
            'assigned_staff' => $validated['assigned_staff'] ?? $validated['assignedStaff'] ?? null,
            'opening_balance'=> (float) ($validated['opening_balance'] ?? $validated['openingBalance'] ?? 0),
            'current_balance'=> (float) ($validated['opening_balance'] ?? $validated['openingBalance'] ?? 0),
            'status'         => $validated['status'] ?? 'active',
            'notes'          => $validated['notes'] ?? null,
            'last_active_at' => now(),
        ]);

        return ResponseHelper::success('POS Terminal created successfully', new PosTerminalResource($terminal), 201);
    }

    /**
     * Display the specified terminal.
     */
    public function show($id)
    {
        $terminal = PosTerminal::findOrFail($id);
        return ResponseHelper::success('POS Terminal retrieved successfully', new PosTerminalResource($terminal));
    }

    /**
     * Update the specified terminal in storage.
     */
    public function update(Request $request, $id)
    {
        $terminal = PosTerminal::findOrFail($id);

        $validated = $request->validate([
            'code'           => 'sometimes|string|max:50|unique:pos_terminals,code,' . $terminal->id,
            'name'           => 'sometimes|string|max:255',
            'counter_type'   => 'nullable|string|max:50',
            'counterType'    => 'nullable|string|max:50',
            'area'           => 'sometimes|string|max:255',
            'branch_code'    => 'nullable|string|max:50',
            'branchCode'     => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'invoice_prefix' => 'nullable|string|max:50',
            'invoicePrefix'  => 'nullable|string|max:50',
            'assigned_staff' => 'nullable|string|max:255',
            'assignedStaff'  => 'nullable|string|max:255',
            'opening_balance'=> 'nullable|numeric|min:0',
            'openingBalance' => 'nullable|numeric|min:0',
            'current_balance'=> 'nullable|numeric',
            'currentBalance' => 'nullable|numeric',
            'status'         => 'nullable|string|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        $updateData = [];
        if (isset($validated['code'])) $updateData['code'] = strtoupper(trim($validated['code']));
        if (isset($validated['name'])) $updateData['name'] = trim($validated['name']);
        if (isset($validated['counter_type']) || isset($validated['counterType'])) {
            $updateData['counter_type'] = $validated['counter_type'] ?? $validated['counterType'];
        }
        if (isset($validated['area'])) $updateData['area'] = trim($validated['area']);
        if (isset($validated['branch_code']) || isset($validated['branchCode'])) {
            $updateData['branch_code'] = $validated['branch_code'] ?? $validated['branchCode'];
        }
        if (isset($validated['address'])) $updateData['address'] = $validated['address'];
        if (isset($validated['invoice_prefix']) || isset($validated['invoicePrefix'])) {
            $updateData['invoice_prefix'] = strtoupper($validated['invoice_prefix'] ?? $validated['invoicePrefix']);
        }
        if (isset($validated['assigned_staff']) || isset($validated['assignedStaff'])) {
            $updateData['assigned_staff'] = $validated['assigned_staff'] ?? $validated['assignedStaff'];
        }
        if (isset($validated['opening_balance']) || isset($validated['openingBalance'])) {
            $updateData['opening_balance'] = (float) ($validated['opening_balance'] ?? $validated['openingBalance']);
        }
        if (isset($validated['current_balance']) || isset($validated['currentBalance'])) {
            $updateData['current_balance'] = (float) ($validated['current_balance'] ?? $validated['currentBalance']);
        }
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (isset($validated['notes'])) $updateData['notes'] = $validated['notes'];

        $terminal->update($updateData);

        return ResponseHelper::success('POS Terminal updated successfully', new PosTerminalResource($terminal));
    }

    /**
     * Remove the specified terminal from storage.
     */
    public function destroy($id)
    {
        $terminal = PosTerminal::findOrFail($id);

        // Protect Main Gold Counter from accidental deletion
        if ($terminal->code === 'POS-GOLD-01' || stripos($terminal->name, 'Main Gold Counter') !== false) {
            return ResponseHelper::error('Main Gold Counter is the primary core terminal and cannot be deleted.', null, 403);
        }

        $terminal->delete();

        return ResponseHelper::success('POS Terminal deleted successfully', null);
    }

    /**
     * Open a session / login to counter.
     */
    public function openSession(Request $request, $id)
    {
        $terminal = PosTerminal::findOrFail($id);

        $validated = $request->validate([
            'cashier_name' => 'required|string|max:255',
            'opening_cash' => 'nullable|numeric|min:0',
        ]);

        $openingCash = (float) ($validated['opening_cash'] ?? $terminal->opening_balance);

        $terminal->update([
            'assigned_staff' => $validated['cashier_name'],
            'current_balance'=> $openingCash,
            'last_active_at' => now(),
            'status'         => 'active',
        ]);

        return ResponseHelper::success('POS Terminal session opened successfully', [
            'terminal'         => new PosTerminalResource($terminal),
            'sessionStartTime' => now()->toISOString(),
            'cashierName'      => $validated['cashier_name'],
            'openingCash'      => $openingCash,
        ]);
    }

    /**
     * Close a session / counter day closing.
     */
    public function closeSession(Request $request, $id)
    {
        $terminal = PosTerminal::findOrFail($id);

        $validated = $request->validate([
            'closing_cash' => 'required|numeric',
            'notes'        => 'nullable|string',
        ]);

        $terminal->update([
            'current_balance' => (float) $validated['closing_cash'],
            'last_active_at'  => now(),
        ]);

        return ResponseHelper::success('POS Terminal session closed successfully', new PosTerminalResource($terminal));
    }
}
