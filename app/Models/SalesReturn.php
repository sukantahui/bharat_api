<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_no',
        'credit_note_date',
        'sale_id',
        'party_id',
        'customer_name',
        'customer_mobile',
        'customer_gstin',
        'original_invoice_no',
        'original_invoice_date',
        'place_of_supply_state_id',
        'place_of_supply_state_code',
        'is_gst_applicable',
        'tax_type',
        'total_gross_weight',
        'total_stone_weight',
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
        'total_credit_amount',
        'refund_mode',
        'refund_payment_id',
        'return_reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'credit_note_date'           => 'date',
            'original_invoice_date'      => 'date',
            'place_of_supply_state_code' => 'integer',
            'is_gst_applicable'          => 'boolean',
            'total_gross_weight'         => 'decimal:3',
            'total_stone_weight'         => 'decimal:3',
            'total_net_weight'           => 'decimal:3',
            'total_fine_gold_weight'     => 'decimal:3',
            'taxable_amount'             => 'decimal:2',
            'cgst_rate'                  => 'decimal:2',
            'cgst_amount'                => 'decimal:2',
            'sgst_rate'                  => 'decimal:2',
            'sgst_amount'                => 'decimal:2',
            'igst_rate'                  => 'decimal:2',
            'igst_amount'                => 'decimal:2',
            'total_tax_amount'           => 'decimal:2',
            'round_off'                  => 'decimal:2',
            'total_credit_amount'        => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function refundPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'refund_payment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
