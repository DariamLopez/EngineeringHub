<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Log as FacadesLog;

class RegisterUserPolicy
{
    /**
     * Solo los usuarios con rol admin pueden registrar usuarios.
     */
    public function create(User $user): bool
    {
        return $user->roles->first()->name == 'admin';
    }

    /**
     * Solo los usuarios con rol admin pueden eliminar usuarios.
     */
    public function delete(User $user): bool
    {
        return $user->roles->first()->name == 'admin';
    }

    public function viewAny(User $user): bool
    {
        if ($user->can('view_users')) {
            return true;
        }
        return false;
    }
}
