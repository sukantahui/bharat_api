<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_note_no',
        'debit_note_date',
        'purchase_id',
        'party_id',
        'original_bill_no',
        'original_bill_date',
        'is_gst_applicable',
        'tax_type',
        'total_gross_weight',
        'total_net_weight',
        'total_fine_gold_weight',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax_amount',
        'round_off',
        'total_debit_amount',
        'settlement_mode',
        'return_reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'debit_note_date'        => 'date',
            'original_bill_date'     => 'date',
            'is_gst_applicable'      => 'boolean',
            'total_gross_weight'     => 'decimal:3',
            'total_net_weight'       => 'decimal:3',
            'total_fine_gold_weight' => 'decimal:3',
            'taxable_amount'         => 'decimal:2',
            'cgst_rate'              => 'decimal:2',
            'cgst_amount'            => 'decimal:2',
            'sgst_rate'              => 'decimal:2',
            'sgst_amount'            => 'decimal:2',
            'igst_rate'              => 'decimal:2',
            'igst_amount'            => 'decimal:2',
            'total_tax_amount'       => 'decimal:2',
            'round_off'              => 'decimal:2',
            'total_debit_amount'     => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
