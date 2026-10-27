<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property int $user_type_id
 * @property int $employee_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserTypeId($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'user_type_id',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    // Relationship with UserType
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    // Relationship with Employee
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user's role name (e.g. "Admin", "Teacher").
     */
    public function getRoleNameAttribute(): ?string
    {
        return $this->userType?->user_type_name;
    }

    /**
     * Check if the user has one or any of the given roles.
     * Supports single string, array of strings, or comma-separated list.
     * Case-insensitive matching.
     *
     * @param string|array ...$roles
     * @return bool
     */
    public function hasRole(string|array ...$roles): bool
    {
        $currentRole = $this->role_name;
        if (!$currentRole) {
            return false;
        }

        // Flatten nested arrays or multiple arguments
        $flatRoles = [];
        foreach ($roles as $role) {
            if (is_array($role)) {
                $flatRoles = array_merge($flatRoles, $role);
            } elseif (is_string($role)) {
                // Support comma-separated e.g. "Admin,Manager" or pipe "Admin|Manager"
                $parts = preg_split('/[,|]/', $role);
                $flatRoles = array_merge($flatRoles, $parts);
            }
        }

        foreach ($flatRoles as $role) {
            if (strcasecmp(trim($role), trim($currentRole)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Quick check if the user is an Administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin', 'Developer', 'Owner');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
