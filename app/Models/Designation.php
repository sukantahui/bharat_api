<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $designation_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\DesignationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereDesignationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Designation extends Model
{
    /** @use HasFactory<\Database\Factories\DesignationFactory> */
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = [
        "inforce","created_at","updated_at"
    ];

    public function employees(){
        return $this->hasMany(Employee::class,'designation_id');
    }
}
