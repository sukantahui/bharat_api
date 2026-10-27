<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JewelleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_code',
        'item_name',
        'category_id',
        'purity_id',
        'hsn_code',
        'gross_weight',
        'stone_weight',
        'net_gold_weight',
        'fine_gold_weight',
        'stone_value',
        'making_charge_type',
        'making_charge_rate',
        'purchase_cost',
        'selling_min_rate_per_gram',
        'huid',
        'source_type',
        'source_reference_id',
        'status',
        'sold_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight'              => 'decimal:3',
            'stone_weight'              => 'decimal:3',
            'net_gold_weight'           => 'decimal:3',
            'fine_gold_weight'          => 'decimal:3',
            'stone_value'               => 'decimal:2',
            'making_charge_rate'        => 'decimal:2',
            'purchase_cost'             => 'decimal:2',
            'selling_min_rate_per_gram' => 'decimal:2',
            'sold_date'                 => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JewelleryItem $item) {
            // Guarantee net gold weight is correctly computed
            $gross = (float) ($item->gross_weight ?? 0);
            $stone = (float) ($item->stone_weight ?? 0);
            $net   = max(0, $gross - $stone);
            $item->net_gold_weight = round($net, 3);

            if (!empty($item->purity_id)) {
                $purity = Purity::find($item->purity_id);
                if ($purity) {
                    $item->fine_gold_weight = round($net * ((float) $purity->purity_percentage / 100), 3);
                }
            }
        });
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
