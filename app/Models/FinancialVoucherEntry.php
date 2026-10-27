<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialVoucherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_voucher_id',
        'account_id',
        'entry_type',
        'amount',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(FinancialVoucher::class, 'financial_voucher_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
