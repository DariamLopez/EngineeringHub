<?php

namespace App\Policies;

use App\Models\Modules;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class ModulesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view_modules')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Modules $modules): bool
    {
        if ($user->can('view_modules')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('edit_modules')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Modules $modules, Request $request): Response
    {
        if ($user->can('edit_modules')) {
        // If the request is trying to update the status, we need to check if the user has the right permissions
        if  ($request->input('status') === \App\Enums\ModuleStatusEnum::VALIDATED->value) {
                $gateResponse = $this->markAsValidated($user, $modules);
                return $gateResponse;
            }
            if ($request->input('status') === \App\Enums\ModuleStatusEnum::READY_FOR_BUILD->value) {
                $gateResponse = $this->markAsValidated($user, $modules);
                if ($gateResponse->allowed()) {
                    // If the module is validated, it can be marked as ready for build
                    return Response::allow();
                } else {
                    // If the module is not validated, it cannot be marked as ready for build
                    return Response::deny('Module must be validated before it can be marked as ready for build.');
                }
            }
            return Response::allow();
        }
        return Response::deny('This action is unautorized');
    }

    /**
     * Determine whether the user can delete any model.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->can('edit_modules')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Modules $modules): bool
    {
        if ($user->can('edit_modules')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Modules $modules): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Modules $modules): bool
    {
        return false;
    }

    public function markAsValidated(User $user, Modules $modules): Response
    {
        $errors = [];
        if($modules->objective == null) {
            $errors[] = 'Objectives are required to validate the module.';
        }
        if($modules->inputs == null || count($modules->inputs) == 0) {
            $errors[] = 'At least one input is required to validate the module.';
        }
        if($modules->outputs == null || count($modules->outputs) == 0) {
            $errors[] = 'At least one output is required to validate the module.';
        }
        if($modules->responsibility == null) {
            $errors[] = 'Responsibility is required to validate the module.';
        }
        if (!empty($errors)) {
            return Response::deny(implode(' ', $errors));
        }
        return Response::allow();
    }
}
