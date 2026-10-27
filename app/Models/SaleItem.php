<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'jewellery_item_id',
        'category_id',
        'purity_id',
        'item_name',
        'tag_code',
        'huid',
        'hsn_code',
        'gross_weight',
        'stone_weight',
        'net_weight',
        'fine_gold_weight',
        'gold_rate_per_gram',
        'gold_amount',
        'making_charge_type',
        'making_charge_rate',
        'making_charges',
        'stone_charges',
        'hallmarking_charge',
        'discount_amount',
        'taxable_amount',
        'gst_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight'       => 'decimal:3',
            'stone_weight'       => 'decimal:3',
            'net_weight'         => 'decimal:3',
            'fine_gold_weight'   => 'decimal:3',
            'gold_rate_per_gram' => 'decimal:2',
            'gold_amount'        => 'decimal:2',
            'making_charge_rate' => 'decimal:2',
            'making_charges'     => 'decimal:2',
            'stone_charges'      => 'decimal:2',
            'hallmarking_charge' => 'decimal:2',
            'discount_amount'    => 'decimal:2',
            'taxable_amount'     => 'decimal:2',
            'gst_rate'           => 'decimal:2',
            'cgst_amount'        => 'decimal:2',
            'sgst_amount'        => 'decimal:2',
            'igst_amount'        => 'decimal:2',
            'total_amount'       => 'decimal:2',
        ];
    }

    public function getRatePerGramAttribute(): float
    {
        return (float) ($this->attributes['gold_rate_per_gram'] ?? 0);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function jewelleryItem(): BelongsTo
    {
        return $this->belongsTo(JewelleryItem::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JewelleryCategory::class);
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(Purity::class);
    }
}
