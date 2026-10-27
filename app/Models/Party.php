<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_type',
        'name',
        'trade_name',
        'mobile',
        'phone',
        'email',
        'address',
        'city',
        'district_id',
        'state_id',
        'state_code',
        'pincode',
        'gst_type',
        'gstin',
        'pan_number',
        'aadhaar_number',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'metal_opening_balance_grams',
        'metal_current_balance_grams',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'state_code'                  => 'integer',
            'opening_balance'             => 'decimal:2',
            'current_balance'             => 'decimal:2',
            'metal_opening_balance_grams' => 'decimal:3',
            'metal_current_balance_grams' => 'decimal:3',
            'is_active'                   => 'boolean',
        ];
    }

    public function getPartyNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setPartyNameAttribute(?string $val): void
    {
        $this->attributes['name'] = $val;
    }


    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
