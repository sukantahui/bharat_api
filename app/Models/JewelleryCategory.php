<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JewelleryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_code',
        'default_hsn_code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(JewelleryItem::class, 'category_id');
    }
}
