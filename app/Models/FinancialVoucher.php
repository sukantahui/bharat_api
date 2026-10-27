<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_date',
        'voucher_type',
        'reference_type',
        'reference_id',
        'total_amount',
        'narration',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FinancialVoucherEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
