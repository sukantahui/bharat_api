<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_code',
        'account_group_id',
        'account_type',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'is_active',
        'is_system',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active'       => 'boolean',
            'is_system'       => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function getIsCashAccountAttribute(): bool
    {
        return $this->account_type === 'cash';
    }

    public function getIsBankAccountAttribute(): bool
    {
        return $this->account_type === 'bank';
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
