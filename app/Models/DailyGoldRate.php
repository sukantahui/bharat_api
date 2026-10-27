<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyGoldRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_date',
        'purity_id',
        'metal_type',
        'selling_rate_per_gram',
        'buying_rate_per_gram',
        'effective_time',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rate_date'             => 'date',
            'selling_rate_per_gram' => 'decimal:2',
            'buying_rate_per_gram'  => 'decimal:2',
        ];
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(Purity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
