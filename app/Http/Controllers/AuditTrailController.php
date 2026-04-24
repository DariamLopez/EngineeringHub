<?php

namespace App\Http\Controllers;

use App\Enums\AuditTrailsEntityTypeEnum;
use App\Models\AuditTrail;
use App\Models\Artifacts;
use App\Models\Modules;
use App\Http\Requests\StoreAuditTrailRequest;
use App\Http\Requests\UpdateAuditTrailRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditTrail::class);

        $query = AuditTrail::query()->with(['actor.roles', 'entity']);
        if ($actor_user_id = $request->query('actor_user_id')) {
            $query->where('actor_user_id', $actor_user_id);
        }
        if ($entity_type = $request->query('entity_type')) {
            $query->where('entity_type', $entity_type);
        }
        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }
        if ($entity_id = $request->query('entity_id')) {
            $query->where('entity_id', $entity_id);
        }
        if ($project_id = $request->query('project_id')) {
            $moduleIds = Modules::where('project_id', $project_id)->pluck('id');
            $artifactIds = Artifacts::where('project_id', $project_id)->pluck('id');

            $deletedModuleIds = AuditTrail::where('entity_type', AuditTrailsEntityTypeEnum::MODULE->value)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(before_json, '$.project_id')) = ?", [$project_id])
                ->pluck('entity_id');

            $deletedArtifactIds = AuditTrail::where('entity_type', AuditTrailsEntityTypeEnum::ARTIFACT->value)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(before_json, '$.project_id')) = ?", [$project_id])
                ->pluck('entity_id');

            $allModuleIds = $moduleIds->merge($deletedModuleIds)->unique();
            $allArtifactIds = $artifactIds->merge($deletedArtifactIds)->unique();

            $query->where(function ($q) use ($project_id, $allModuleIds, $allArtifactIds) {
                $q->where(function ($q) use ($project_id) {
                    $q->where('entity_type', AuditTrailsEntityTypeEnum::PROJECT->value)
                      ->where('entity_id', $project_id);
                })
                ->orWhere(function ($q) use ($allModuleIds) {
                    $q->where('entity_type', AuditTrailsEntityTypeEnum::MODULE->value)
                      ->whereIn('entity_id', $allModuleIds);
                })
                ->orWhere(function ($q) use ($allArtifactIds) {
                    $q->where('entity_type', AuditTrailsEntityTypeEnum::ARTIFACT->value)
                      ->whereIn('entity_id', $allArtifactIds);
                });
            });
        }

        $order_by = $request->query('order_by', 'id');
        $order_dir = $request->query('order_dir', 'desc');

        if ($per_page = $request->query('per_page')) {
            $audit_trails = $query->orderBy($order_by, $order_dir)->paginate($per_page);
        } else {
            $audit_trails = $query->orderBy($order_by, $order_dir)->get();
        }
        //$artifact = Artifact::all();
        return response()->json($audit_trails);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuditTrailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuditTrailRequest $request, AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditTrail $auditTrail)
    {
        //
    }
}
