<?php

namespace App\Http\Controllers;

use App\Models\Artifacts;
use App\Http\Requests\StoreArtifactsRequest;
use App\Http\Requests\UpdateArtifactsRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ArtifactsController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$this->authorize('viewAny', Artifacts::class);

        $query = Artifacts::query()->with('project');
        if ($projects_id = $request->query('projects_id')) {
            $query->where('projects_id', $projects_id);
        }
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
        //$artifact = Artifact::all();
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
        $this->authorize('create', Artifacts::class);
        $artifact = Artifacts::create($request->validated());
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
        $artifacts->update($request->validated());
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
        $this->authorize('delete', $artifacts);
        $artifacts->delete();
        return response()->json([
            'message' => 'Artifact deleted successfully',
            ], 200);
    }
}
