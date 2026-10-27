<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purity extends Model
{
    use HasFactory;

    protected $fillable = [
        'karat_name',
        'karat_code',
        'purity_percentage',
        'hallmark_code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purity_percentage' => 'decimal:3',
            'is_active'         => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(JewelleryItem::class, 'purity_id');
    }

    public function dailyRates(): HasMany
    {
        return $this->hasMany(DailyGoldRate::class, 'purity_id');
    }
}
