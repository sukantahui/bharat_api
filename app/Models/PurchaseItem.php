<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'jewellery_item_id',
        'category_id',
        'purity_id',
        'item_description',
        'hsn_code',
        'gross_weight',
        'stone_weight',
        'net_weight',
        'purity_percentage',
        'fine_gold_weight',
        'rate_per_gram',
        'gold_amount',
        'making_charges',
        'stone_charges',
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
            'gross_weight'      => 'decimal:3',
            'stone_weight'      => 'decimal:3',
            'net_weight'        => 'decimal:3',
            'purity_percentage' => 'decimal:3',
            'fine_gold_weight'  => 'decimal:3',
            'rate_per_gram'     => 'decimal:2',
            'gold_amount'       => 'decimal:2',
            'making_charges'    => 'decimal:2',
            'stone_charges'     => 'decimal:2',
            'taxable_amount'    => 'decimal:2',
            'gst_rate'          => 'decimal:2',
            'cgst_amount'       => 'decimal:2',
            'sgst_amount'       => 'decimal:2',
            'igst_amount'       => 'decimal:2',
            'total_amount'      => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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
