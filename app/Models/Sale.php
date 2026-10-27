<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'party_id',
        'customer_name',
        'customer_mobile',
        'customer_email',
        'customer_address',
        'customer_gstin',
        'customer_pan',
        'place_of_supply_state_id',
        'place_of_supply_state_code',
        'invoice_type',
        'is_gst_applicable',
        'tax_type',
        'total_gross_weight',
        'total_stone_weight',
        'total_net_weight',
        'total_fine_gold_weight',
        'total_gold_amount',
        'total_making_charges',
        'total_stone_charges',
        'hallmarking_charges',
        'other_charges',
        'discount_amount',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax_amount',
        'old_gold_exchange_amount',
        'round_off',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'is_cancelled',
        'cancellation_reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'                => 'date',
            'place_of_supply_state_code'  => 'integer',
            'is_gst_applicable'           => 'boolean',
            'total_gross_weight'          => 'decimal:3',
            'total_stone_weight'          => 'decimal:3',
            'total_net_weight'            => 'decimal:3',
            'total_fine_gold_weight'      => 'decimal:3',
            'total_gold_amount'           => 'decimal:2',
            'total_making_charges'        => 'decimal:2',
            'total_stone_charges'         => 'decimal:2',
            'hallmarking_charges'         => 'decimal:2',
            'other_charges'               => 'decimal:2',
            'discount_amount'             => 'decimal:2',
            'taxable_amount'              => 'decimal:2',
            'cgst_rate'                   => 'decimal:2',
            'cgst_amount'                 => 'decimal:2',
            'sgst_rate'                   => 'decimal:2',
            'sgst_amount'                 => 'decimal:2',
            'igst_rate'                   => 'decimal:2',
            'igst_amount'                 => 'decimal:2',
            'total_tax_amount'            => 'decimal:2',
            'old_gold_exchange_amount'    => 'decimal:2',
            'round_off'                   => 'decimal:2',
            'grand_total'                 => 'decimal:2',
            'paid_amount'                 => 'decimal:2',
            'balance_amount'              => 'decimal:2',
            'is_cancelled'                => 'boolean',
        ];
    }

    public function getInvoiceNoAttribute(): string
    {
        return $this->attributes['invoice_number'] ?? '';
    }

    public function getNetPayableAmountAttribute(): float
    {
        return (float) ($this->attributes['grand_total'] ?? 0);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function placeOfSupplyState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'place_of_supply_state_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function oldGoldExchanges(): HasMany
    {
        return $this->hasMany(SaleOldGoldExchange::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'reference_id')->where('reference_type', 'sale');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
