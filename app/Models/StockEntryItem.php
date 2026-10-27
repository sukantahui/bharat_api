<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_entry_id',
        'jewellery_item_id',
        'category_id',
        'purity_id',
        'item_name',
        'tag_code',
        'huid',
        'gross_weight',
        'stone_weight',
        'net_weight',
        'fine_weight',
        'estimated_rate_per_gram',
        'making_cost',
        'stone_cost',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight'            => 'decimal:3',
            'stone_weight'            => 'decimal:3',
            'net_weight'              => 'decimal:3',
            'fine_weight'             => 'decimal:3',
            'estimated_rate_per_gram' => 'decimal:2',
            'making_cost'             => 'decimal:2',
            'stone_cost'              => 'decimal:2',
            'total_cost'              => 'decimal:2',
        ];
    }

    public function stockEntry(): BelongsTo
    {
        return $this->belongsTo(StockEntry::class, 'stock_entry_id');
    }

    public function jewelleryItem(): BelongsTo
    {
        return $this->belongsTo(JewelleryItem::class, 'jewellery_item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JewelleryCategory::class, 'category_id');
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(Purity::class, 'purity_id');
    }
}
