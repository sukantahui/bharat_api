<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_no',
        'purchase_date',
        'vendor_bill_no',
        'vendor_bill_date',
        'party_id',
        'purchase_type',
        'is_gst_applicable',
        'tax_type',
        'total_gross_weight',
        'total_net_weight',
        'total_fine_gold_weight',
        'total_gold_amount',
        'total_making_charges',
        'total_stone_charges',
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
        'round_off',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'is_cancelled',
        'cancellation_reason',
        'itc_eligibility',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date'          => 'date',
            'vendor_bill_date'       => 'date',
            'is_gst_applicable'      => 'boolean',
            'is_cancelled'           => 'boolean',
            'total_gross_weight'     => 'decimal:3',
            'total_net_weight'       => 'decimal:3',
            'total_fine_gold_weight' => 'decimal:3',
            'total_gold_amount'      => 'decimal:2',
            'total_making_charges'   => 'decimal:2',
            'total_stone_charges'    => 'decimal:2',
            'other_charges'          => 'decimal:2',
            'discount_amount'        => 'decimal:2',
            'taxable_amount'         => 'decimal:2',
            'cgst_rate'              => 'decimal:2',
            'cgst_amount'            => 'decimal:2',
            'sgst_rate'              => 'decimal:2',
            'sgst_amount'            => 'decimal:2',
            'igst_rate'              => 'decimal:2',
            'igst_amount'            => 'decimal:2',
            'total_tax_amount'       => 'decimal:2',
            'round_off'              => 'decimal:2',
            'grand_total'            => 'decimal:2',
            'paid_amount'            => 'decimal:2',
            'balance_amount'         => 'decimal:2',
        ];
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
