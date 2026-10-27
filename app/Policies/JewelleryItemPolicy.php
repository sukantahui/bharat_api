<?php

namespace App\Policies;

use App\Models\JewelleryItem;
use App\Models\User;

class JewelleryItemPolicy
{
    /**
     * Determine whether the user can view any items.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Teacher', 'Worker');
    }

    /**
     * Determine whether the user can view the item.
     */
    public function view(User $user, JewelleryItem $item): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Teacher', 'Worker');
    }

    /**
     * Determine whether the user can create items.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager');
    }

    /**
     * Determine whether the user can update the item.
     */
    public function update(User $user, JewelleryItem $item): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager');
    }

    /**
     * Determine whether the user can delete the item.
     */
    public function delete(User $user, JewelleryItem $item): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner');
    }
}
