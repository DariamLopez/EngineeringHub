<?php

namespace App\Policies;

use App\Models\Artifacts;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArtifactsPolicy

{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view_artifacts')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Artifacts $artifacts): bool
    {
        if ($user->can('view_artifacts')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('edit_artifacts')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Artifacts $artifacts, Request $request): Response
    {
        if ($user->can('edit_artifacts')) {
            // Si el status se está cambiando a 'done', aplicamos las gates adicionales
            if ($request->input('status') === \App\Enums\ArtifactStatusEnum::DONE->value) {
                $gateResponse = $this->markAsDone($user, $artifacts);
                Log::info('Gate response: '.$gateResponse->message());
                return $gateResponse;
            }
            return Response::allow();
        }
        return Response::deny('This action is unauthorized.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Artifacts $artifacts): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Artifacts $artifacts): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Artifacts $artifacts): bool
    {
        return false;
    }
    /**
     * Gate 1: No puedes marcar domain_breakdown como done si big_picture no está done
     * Gate 2: No puedes marcar module_matrix como done si domain_breakdown no está done
     * Gate 3: No puedes marcar system_architecture como done si no hay al menos N módulos validados
     * Estas gates asumen que el cambio de status a 'done' se realiza vía update.
     */
    public function markAsDone(User $user, Artifacts $artifacts): Response
    {
        $type = $artifacts->type;
        $project = $artifacts->project;
        if (!$project) {
            return Response::deny('No se puede validar dependencias: artifact sin proyecto.');
        }
        // Gate 1
        if ($type === \App\Enums\ArtifactTypeEnum::DOMAIN_BREAKDOWN->value) {
            $bigPicture = $project->artifacts()->where('type', \App\Enums\ArtifactTypeEnum::BIG_PICTURE->value)->first();
            if (!$bigPicture || $bigPicture->status !== \App\Enums\ArtifactStatusEnum::DONE->value) {
                return Response::deny('No puedes marcar domain_breakdown como done si big_picture no está done.');
            }
        }
        // Gate 2
        if ($type === \App\Enums\ArtifactTypeEnum::MODULE_MATRIX->value) {
            $domainBreakdown = $project->artifacts()->where('type', \App\Enums\ArtifactTypeEnum::DOMAIN_BREAKDOWN->value)->first();
            if (!$domainBreakdown || $domainBreakdown->status !== \App\Enums\ArtifactStatusEnum::DONE->value) {
                return Response::deny('No puedes marcar module_matrix como done si domain_breakdown no está done.');
            }
        }
        // Gate 3
        if ($type === \App\Enums\ArtifactTypeEnum::SYSTEM_ARCHITECTURE->value) {
            $minModules = config('artifact_rules.min_validated_modules', 3);
            $validatedModules = $project->modules()->where('status', 'validated')->count();
            if ($validatedModules < $minModules) {
                return Response::deny("No puedes marcar system_architecture como done si no hay al menos $minModules módulos validados.");
            }
        }
        return Response::allow();
    }
}
