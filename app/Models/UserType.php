<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $user_type_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\UserTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereUserTypeName($value)
 * @mixin \Eloquent
 */
class UserType extends Model
{
    /** @use HasFactory<\Database\Factories\UserTypeFactory> */
    use HasFactory;
    protected $guarded = ['id'];
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
