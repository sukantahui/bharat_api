<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    /** @use HasFactory<\Database\Factories\StateFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
