<?php

namespace App\Http\Controllers;

use App\Enums\AuditTrailsActionsEnum;
use App\Enums\AuditTrailsEntityTypeEnum;
use App\Models\Artifacts;
use App\Http\Requests\StoreArtifactsRequest;
use App\Http\Requests\UpdateArtifactsRequest;
use App\Models\AuditTrail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\IndexArtifactsRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ArtifactsController extends Controller

{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     *
     */
    public function index(IndexArtifactsRequest $request)
    {
        $this->authorize('viewAny', Artifacts::class);

        $project_id = $request->validated('project_id');
        //Log::info("Fetching artifacts for project_id: $project_id with filters: " . json_encode($request->validated()));
        $query = Artifacts::query()->with('owner')->with('project')->where('project_id', $project_id);
        if ($artifact_type = $request->query('type')) {
            $query->where('type', $artifact_type);
        }
        if ($artifact_status = $request->query('status')) {
            $query->where('status', $artifact_status);
        }
        if ($artifact_owner = $request->query('owner_user_id')) {
            $query->where('owner_user_id', $artifact_owner);
        }

        $order_by = $request->query('order_by', 'id');
        $order_dir = $request->query('order_dir', 'desc');

        if ($per_page = $request->query('per_page')) {
            $artifact = $query->orderBy($order_by, $order_dir)->paginate($per_page);
        } else {
            $artifact = $query->orderBy($order_by, $order_dir)->get();
        }

        $items = $per_page ? $artifact->getCollection() : $artifact;
        $items->transform(function ($item) {
            $item->content_json = $item->json;
            return $item;
        });

        return response()->json($artifact);
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
    public function store(StoreArtifactsRequest $request)
    {
        $data = $request->validated();
        Log::info("Store Artifacts Request Data", $data);
        $this->authorize('create', [Artifacts::class, $request]);
        $data['owner_user_id'] = $request->user()->id;
        $artifact = Artifacts::create($data);
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::ARTIFACT->value,
            $artifact->id,
            AuditTrailsActionsEnum::CREATED->value,
            null,
            $artifact->toArray()
        );
        return response()->json([
            'message' => 'Artifact created successfully',
            'data' => $artifact,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Artifacts $artifacts)
    {
        $this->authorize('view', $artifacts);
        $artifacts->load(['owner', 'project']);
        $artifacts->content_json = $artifacts->json;
        return response()->json($artifacts);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Artifacts $artifacts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArtifactsRequest $request, Artifacts $artifacts)
    {
        $this->authorize('update', [$artifacts, $request]);
        $action = '';
        if ($request->validated('status') != $artifacts->status) {
            $action = AuditTrailsActionsEnum::STATUS_CHANGED->value;
        } else {
            $action = AuditTrailsActionsEnum::UPDATED->value;
        }
        $before_artifact = $artifacts->replicate();
        $artifacts->update($request->validated());
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::ARTIFACT->value,
            $artifacts->id,
            $action,
            $before_artifact->toArray(),
            $artifacts->toArray()
        );

        return response()->json([
            'message' => 'Artifact updated successfully',
            'data' => $artifacts,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Artifacts $artifacts)
    {

        AuditTrail::logAction(
            request()->user()->id,
            AuditTrailsEntityTypeEnum::ARTIFACT->value,
            $artifacts->id,
            AuditTrailsActionsEnum::DELETED->value,
            $artifacts->toArray(),
            null
        );
        $this->authorize('delete', $artifacts);
        $artifacts->delete();
        return response()->json([
            'message' => 'Artifact deleted successfully',
        ], 200);
    }
}
