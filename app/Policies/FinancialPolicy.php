<?php

namespace App\Policies;

use App\Models\User;

class FinancialPolicy
{
    /**
     * View accounting, cash balances, and GST reports.
     */
    public function viewFinancials(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager');
    }

    /**
     * Post expenses and payments.
     */
    public function managePayments(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner', 'Manager', 'Worker');
    }

    /**
     * Delete or reverse financial transactions.
     */
    public function deleteFinancials(User $user): bool
    {
        return $user->hasRole('Admin', 'Developer', 'Owner');
    }
}
