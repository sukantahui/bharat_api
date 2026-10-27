<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTerminal extends Model
{
    use HasFactory;

    protected $table = 'pos_terminals';

    protected $fillable = [
        'code',
        'name',
        'counter_type',
        'area',
        'branch_code',
        'address',
        'invoice_prefix',
        'assigned_staff',
        'opening_balance',
        'current_balance',
        'status',
        'notes',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'last_active_at'  => 'datetime',
        ];
    }
}
