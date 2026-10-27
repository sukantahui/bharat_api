<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseIncome extends Model
{
    use HasFactory;

    protected $table = 'expense_incomes';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_type',
        'account_id',
        'payment_account_id',
        'amount',
        'payment_mode',
        'reference_no',
        'description',
        'is_gst_claimed',
        'tax_amount',
        'vendor_gstin',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date'     => 'date',
            'amount'         => 'decimal:2',
            'is_gst_claimed' => 'boolean',
            'tax_amount'     => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
