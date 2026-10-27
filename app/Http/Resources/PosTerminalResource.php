<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosTerminalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => (string) $this->id,
            'code'           => $this->code,
            'name'           => $this->name,
            'counterType'    => $this->counter_type,
            'counter_type'   => $this->counter_type,
            'area'           => $this->area,
            'branchCode'     => $this->branch_code,
            'branch_code'    => $this->branch_code,
            'address'        => $this->address,
            'invoicePrefix'  => $this->invoice_prefix,
            'invoice_prefix' => $this->invoice_prefix,
            'assignedStaff'  => $this->assigned_staff,
            'assigned_staff' => $this->assigned_staff,
            'openingBalance' => (float) $this->opening_balance,
            'opening_balance'=> (float) $this->opening_balance,
            'currentBalance' => (float) $this->current_balance,
            'current_balance'=> (float) $this->current_balance,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'lastActiveAt'   => $this->last_active_at?->toISOString(),
            'last_active_at' => $this->last_active_at?->toISOString(),
            'createdAt'      => $this->created_at?->toISOString(),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
