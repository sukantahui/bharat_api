<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @property int $id
 * @property string $employee_nme
 * @property string $mobile
 * @property string|null $email
 * @property int $department_id
 * @property int $designation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\EmployeeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmployeeNme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $guarded = ['id'];


    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Relationship with Designation
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    // Relationship with User (One-to-One)
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

}
