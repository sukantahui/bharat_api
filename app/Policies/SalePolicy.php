<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Teacher', 'Worker');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Teacher', 'Worker');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Worker');
    }

    public function cancel(User $user, Sale $sale): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager');
    }
}
