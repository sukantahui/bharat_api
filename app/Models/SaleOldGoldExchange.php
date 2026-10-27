<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleOldGoldExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_description',
        'gross_weight',
        'dust_stone_weight',
        'net_weight',
        'purity_percentage',
        'fine_gold_weight',
        'buying_rate_per_gram',
        'total_exchange_value',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight'         => 'decimal:3',
            'dust_stone_weight'    => 'decimal:3',
            'net_weight'           => 'decimal:3',
            'purity_percentage'    => 'decimal:3',
            'fine_gold_weight'     => 'decimal:3',
            'buying_rate_per_gram' => 'decimal:2',
            'total_exchange_value' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
