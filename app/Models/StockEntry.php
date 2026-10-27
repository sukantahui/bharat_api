<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_type',
        'artisan_id',
        'total_items_count',
        'total_gross_weight',
        'total_stone_weight',
        'total_net_weight',
        'total_fine_gold_weight',
        'total_estimated_valuation',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date'                => 'date',
            'total_items_count'         => 'integer',
            'total_gross_weight'        => 'decimal:3',
            'total_stone_weight'        => 'decimal:3',
            'total_net_weight'          => 'decimal:3',
            'total_fine_gold_weight'    => 'decimal:3',
            'total_estimated_valuation' => 'decimal:2',
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'artisan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockEntryItem::class, 'stock_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
