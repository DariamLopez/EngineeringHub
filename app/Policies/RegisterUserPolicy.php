<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Log as FacadesLog;

class RegisterUserPolicy
{
    /**
     * Only users with admin role can create new users.
     */
    public function create(User $user): bool
    {
        return $user->roles->first()->name == 'admin';
    }

    /**
     * Only users with admin role can delete users.
     */
    public function delete(User $user): bool
    {
        return $user->roles->first()->name == 'admin';
    }

    /**
     * Only users with admin role can view users.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view_users')) {
            return true;
        }
        return false;
    }
}
