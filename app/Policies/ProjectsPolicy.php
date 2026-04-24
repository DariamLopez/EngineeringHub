<?php

namespace App\Policies;

use App\Models\Projects;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class ProjectsPolicy

{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view_projects')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Projects $projects): bool
    {
        if ($user->can('view_projects')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('edit_projects')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Projects $projects, Request $request): Response
    {
        if ($user->can('edit_projects')) {
            if ($request->input('status') === \App\Enums\ProjectStatusEnum::EXECUTION->value) {
                $gateResponse = $this->moveToExecution($user, $projects);
                return $gateResponse;
            }
            return Response::allow();
        }
        return Response::deny('This action is unauthorized.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Projects $projects): bool
    {
        if ($user->can('edit_projects')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Projects $projects): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Projects $projects): bool
    {
        return false;
    }
    /**
     * Gate 4: You cannot move the project to execution if the following aspects are not completed: strategic alignment,
     * big picture, domain breakdown and module matrix.
     */
    public function moveToExecution(User $user, Projects $project): Response
    {
        if ($project->status !== \App\Enums\ProjectStatusEnum::DISCOVERY->value) {
            return Response::allow(); // Only applies when moving from discovery to execution
        }
        $requiredTypes = [
            \App\Enums\ArtifactTypeEnum::STRATEGIC_ALIGNMENT->value,
            \App\Enums\ArtifactTypeEnum::BIG_PICTURE->value,
            \App\Enums\ArtifactTypeEnum::DOMAIN_BREAKDOWN->value,
            \App\Enums\ArtifactTypeEnum::MODULE_MATRIX->value,
        ];
        $artifacts = $project->artifacts()->whereIn('type', $requiredTypes)->get();
        $missing = [];
        foreach ($requiredTypes as $type) {
            $artifact = $artifacts->firstWhere('type', $type);
            if (!$artifact || $artifact->status !== \App\Enums\ArtifactStatusEnum::DONE->value) {
                $missing[] = $type;
            }
        }
        if (!empty($missing)) {
            return Response::deny('You cannot move the project to execution. The following required aspects must be completed: '.implode(', ', $missing));
        }
        return Response::allow();
    }
}
